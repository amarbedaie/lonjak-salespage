<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;

/**
 * EasyParcel Malaysia integration (Individual API v1.3.0.0).
 *
 * Flow: rates() → book() (EPSubmitOrderBulk then EPPayOrderBulk) → AWB + tracking URL.
 * Each merchant uses their OWN EasyParcel API key + credit, mirroring the per-merchant BayarCash setup.
 * Endpoints are form-encoded (http_build_query); the `bulk` param is an array of parcels.
 */
class EasyParcelService
{
    public function __construct(
        private string $apiKey,
        private bool $sandbox = true,
    ) {}

    /** Build from a merchant's saved credentials, or null if EasyParcel isn't configured. */
    public static function forMerchant(User $user): ?self
    {
        $key = $user->easyparcel_api_key;
        if (empty($key)) {
            return null;
        }

        return new self($key, (bool) $user->easyparcel_sandbox);
    }

    private function base(): string
    {
        return $this->sandbox
            ? 'https://demo.connect.easyparcel.my/'
            : 'https://connect.easyparcel.my/';
    }

    /** POST an action; returns the decoded JSON (or an error-shaped array on transport failure). */
    private function call(string $action, array $params): array
    {
        try {
            $res = Http::asForm()->timeout(30)->post($this->base().'?ac='.$action, array_merge(['api' => $this->apiKey], $params));
            $json = $res->json();

            return is_array($json) ? $json : ['api_status' => 'Error', 'error_remark' => 'Respons tidak sah dari EasyParcel.'];
        } catch (\Throwable $e) {
            return ['api_status' => 'Error', 'error_remark' => 'Gagal hubungi EasyParcel: '.$e->getMessage()];
        }
    }

    /**
     * Rate-check all couriers for a shipment.
     *
     * @return array{ok:bool, error?:string, rates:array<int,array<string,mixed>>}
     */
    public function rates(array $from, array $to, float $weight): array
    {
        $resp = $this->call('EPRateCheckingBulk', [
            'bulk' => [[
                'pick_code' => $from['postcode'],
                'pick_state' => self::stateCode($from['state']),
                'pick_country' => 'MY',
                'send_code' => $to['postcode'],
                'send_state' => self::stateCode($to['state']),
                'send_country' => 'MY',
                'weight' => $weight,
            ]],
            'exclude_fields' => ['rates.*.dropoff_point', 'rates.*.pickup_point', 'pgeon_point'],
        ]);

        if (($resp['api_status'] ?? '') !== 'Success') {
            return ['ok' => false, 'error' => $resp['error_remark'] ?? 'Gagal semak kadar.', 'rates' => []];
        }

        $rates = $resp['result'][0]['rates'] ?? [];
        $out = [];
        foreach ($rates as $r) {
            $out[] = [
                'service_id' => $r['service_id'] ?? '',
                'courier' => $r['courier_name'] ?? ($r['service_name'] ?? 'Kurier'),
                'service_name' => $r['service_name'] ?? '',
                'price' => (float) ($r['price'] ?? 0),
                'delivery' => $r['delivery'] ?? '',
                'detail' => $r['service_detail'] ?? '',
            ];
        }
        // Cheapest first.
        usort($out, fn ($a, $b) => $a['price'] <=> $b['price']);

        return ['ok' => true, 'rates' => $out];
    }

    /**
     * Submit + pay a shipment for an order. On success the parcel is booked and an AWB is issued.
     *
     * @return array{ok:bool, error?:string, courier?:string, awb?:string, awb_link?:string, tracking_url?:string, ep_order_no?:string, price?:float}
     */
    public function book(Order $order, User $merchant, string $serviceId, float $weight, string $content, float $value): array
    {
        $submit = $this->call('EPSubmitOrderBulk', [
            'bulk' => [[
                'weight' => $weight,
                'content' => mb_substr($content, 0, 35),
                'value' => $value,
                'service_id' => $serviceId,
                // Sender (merchant pickup)
                'pick_name' => mb_substr($merchant->ship_name ?: $merchant->business_name ?: $merchant->name, 0, 35),
                'pick_contact' => self::phone($merchant->ship_phone ?: $merchant->phone),
                'pick_mobile' => self::phone($merchant->ship_phone ?: $merchant->phone),
                'pick_addr1' => mb_substr($merchant->ship_addr1 ?? '', 0, 35),
                'pick_addr2' => mb_substr($merchant->ship_addr2 ?? '', 0, 35),
                'pick_city' => mb_substr($merchant->ship_city ?? '', 0, 35),
                'pick_state' => self::stateCode($merchant->ship_state ?? ''),
                'pick_code' => $merchant->ship_postcode ?? '',
                'pick_country' => 'MY',
                // Receiver (customer)
                'send_name' => mb_substr($order->customer, 0, 35),
                'send_contact' => self::phone($order->phone),
                'send_mobile' => self::phone($order->phone),
                'send_addr1' => mb_substr($order->address, 0, 35),
                'send_addr2' => mb_substr(mb_substr($order->address, 35), 0, 35),
                'send_city' => mb_substr($order->state, 0, 35),
                'send_state' => self::stateCode($order->state),
                'send_code' => self::postcodeFrom($order->address),
                'send_country' => 'MY',
                'collect_date' => now()->format('Y-m-d'),
                'sms' => 'false',
                'send_email' => $order->email ?: ($merchant->email),
                'reference' => 'ORDER-'.$order->id,
            ]],
        ]);

        if (($submit['api_status'] ?? '') !== 'Success') {
            return ['ok' => false, 'error' => $submit['error_remark'] ?? 'Gagal hantar order ke EasyParcel.'];
        }
        $row = $submit['result'][0] ?? [];
        if (($row['status'] ?? '') !== 'Success' || empty($row['order_number'])) {
            return ['ok' => false, 'error' => $row['remarks'] ?? 'Order EasyParcel gagal dibuat.'];
        }
        $orderNo = $row['order_number'];
        $courier = $row['courier'] ?? '';

        // Pay for the parcel from the merchant's EasyParcel credit → issues the AWB.
        $pay = $this->call('EPPayOrderBulk', ['bulk' => [['order_no' => $orderNo]]]);
        if (($pay['api_status'] ?? '') !== 'Success') {
            return ['ok' => false, 'error' => $pay['error_remark'] ?? 'Order dibuat tetapi bayaran EasyParcel gagal (semak kredit EasyParcel anda).', 'ep_order_no' => $orderNo];
        }
        $pr = $pay['result'][0] ?? [];
        $parcel = $pr['parcel'][0] ?? [];

        return [
            'ok' => true,
            'courier' => $courier,
            'ep_order_no' => $orderNo,
            'awb' => $parcel['awb'] ?? '',
            'awb_link' => $parcel['awb_id_link'] ?? '',
            'tracking_url' => $parcel['tracking_url'] ?? '',
            'price' => (float) ($row['price'] ?? 0),
        ];
    }

    /** Map a Malaysian state name to the EasyParcel short code (Appendix III). */
    public static function stateCode(string $state): string
    {
        $s = strtolower(trim($state));
        $map = [
            'johor' => 'jhr', 'kedah' => 'kdh', 'kelantan' => 'ktn', 'melaka' => 'mlk', 'malacca' => 'mlk',
            'negeri sembilan' => 'nsn', 'pahang' => 'phg', 'perak' => 'prk', 'perlis' => 'pls',
            'pulau pinang' => 'png', 'penang' => 'png', 'selangor' => 'sgr', 'terengganu' => 'trg',
            'kuala lumpur' => 'kul', 'wp kuala lumpur' => 'kul', 'putrajaya' => 'pjy', 'putra jaya' => 'pjy',
            'sarawak' => 'srw', 'sabah' => 'sbh', 'labuan' => 'lbn', 'wp labuan' => 'lbn',
        ];
        if (isset($map[$s])) {
            return $map[$s];
        }
        // Already a code, or unknown — pass through lowercased (EasyParcel validates it).
        return in_array($s, $map, true) ? $s : $s;
    }

    /** Normalise a phone to digits (EasyParcel expects a plain contact number). */
    private static function phone(?string $p): string
    {
        return preg_replace('/\D/', '', (string) $p) ?: '0123456789';
    }

    /** Best-effort extract a 5-digit Malaysian postcode from a free-form address. */
    private static function postcodeFrom(string $address): string
    {
        return preg_match('/\b(\d{5})\b/', $address, $m) ? $m[1] : '';
    }
}
