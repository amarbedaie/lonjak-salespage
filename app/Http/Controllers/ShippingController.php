<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\EasyParcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingController extends Controller
{
    public function index()
    {
        return view('dashboard.shipping', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'easyparcel_api_key' => 'nullable|string|max:120',
            'easyparcel_sandbox' => 'nullable|boolean',
            'ship_name' => 'nullable|string|max:120',
            'ship_phone' => 'nullable|string|max:30',
            'ship_addr1' => 'nullable|string|max:120',
            'ship_addr2' => 'nullable|string|max:120',
            'ship_city' => 'nullable|string|max:60',
            'ship_state' => 'nullable|string|max:60',
            'ship_postcode' => 'nullable|string|max:10',
        ]);
        $data['easyparcel_sandbox'] = $request->boolean('easyparcel_sandbox');

        // Keep the existing key if the field was left blank (so we never wipe a saved key).
        if (empty($data['easyparcel_api_key'])) {
            unset($data['easyparcel_api_key']);
        }

        Auth::user()->update($data);

        return back()->with('ok', 'Tetapan penghantaran disimpan.');
    }

    /** AJAX: rate-check couriers for an order. */
    public function rates(Order $order, Request $request)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        $ep = EasyParcelService::forMerchant(Auth::user());
        if (! $ep) {
            return response()->json(['ok' => false, 'error' => 'Sila sambung EasyParcel di tetapan Penghantaran dahulu.']);
        }
        $merchant = Auth::user();
        if (empty($merchant->ship_postcode) || empty($merchant->ship_state)) {
            return response()->json(['ok' => false, 'error' => 'Sila lengkapkan alamat pickup (poskod & negeri) di tetapan Penghantaran.']);
        }

        $weight = max(0.1, (float) $request->input('weight', 1));
        $toPostcode = trim((string) $request->input('postcode', ''));

        $result = $ep->rates(
            ['postcode' => $merchant->ship_postcode, 'state' => $merchant->ship_state],
            ['postcode' => $toPostcode, 'state' => $order->state],
            $weight,
        );

        return response()->json($result);
    }

    /** Book + pay a shipment; persist the AWB + tracking on the order. */
    public function book(Order $order, Request $request)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        $data = $request->validate([
            'service_id' => 'required|string|max:30',
            'weight' => 'required|numeric|min:0.1',
            'postcode' => 'required|string|max:10',
        ]);

        $ep = EasyParcelService::forMerchant(Auth::user());
        if (! $ep) {
            return back()->withErrors(['ship' => 'EasyParcel belum disambung.']);
        }

        // EasyParcel needs the destination postcode; stash it onto the address if missing so book() can read it.
        if (! str_contains($order->address, $data['postcode'])) {
            $order->address = rtrim($order->address, ', ').', '.$data['postcode'];
        }

        $res = $ep->book(
            $order,
            Auth::user(),
            $data['service_id'],
            (float) $data['weight'],
            $order->product_name ?: 'Barang',
            (float) $order->total,
        );

        if (empty($res['ok'])) {
            return back()->withErrors(['ship' => $res['error'] ?? 'Tempahan penghantaran gagal.']);
        }

        $order->update([
            'courier' => $res['courier'] ?? $order->courier,
            'awb' => $res['awb'] ?? null,
            'awb_link' => $res['awb_link'] ?? null,
            'tracking_url' => $res['tracking_url'] ?? null,
            'ep_order_no' => $res['ep_order_no'] ?? null,
            'ship_price' => $res['price'] ?? null,
            'ship_weight' => (float) $data['weight'],
            'status' => 'diproses',
        ]);

        return back()->with('ok', 'Penghantaran ditempah! AWB '.($res['awb'] ?? '').' dijana.');
    }
}
