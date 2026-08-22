# Provisioning — akses automatik lepas bayar

Bila pelanggan bayar salespage yang terikat pada satu system BeDaie, Mendap terus
membuka akaun premium mereka di system itu dan WhatsApp magic login link.

## Aliran

```
BayarCash webhook → PaymentController@callback → event(OrderPaid)
                                                    ├── SendPaidWhatsAppNotifications   (resit)
                                                    └── ProvisionPurchasedSystemAccess  (akses)
                                                          ├── POST <system>/api/provision
                                                          ├── simpan dalam provisioning_calls
                                                          └── WhatsApp magic link ke pelanggan
```

Kedua-dua listener `ShouldQueue`, jadi ia jadi *job* berasingan pada queue
`database` — satu gagal tidak menghalang satu lagi.

## Kontrak (sama untuk semua 10 system)

```
POST https://<system>/api/provision
Authorization: Bearer <PROVISIONING_API_KEY>
X-Signature:   HMAC-SHA256(RAW body, PROVISIONING_SIGNING_SECRET)   // hex huruf kecil
Content-Type:  application/json

{
  "idempotency_key": "mendap-order-123-statushadis",
  "order_ref": "MENDAP-123",
  "order_id": 123,
  "customer": {"email": "...", "name": "...", "phone": "..."}
}

→ 200 {"external_user_id": "42", "login_url": "...", "username": "...", "magic_link": "..."}
```

Signature dikira atas **bait mentah yang dihantar** — sebab tu `ProvisioningClient`
`json_encode` sekali sahaja dan guna semula string itu (`withBody`). Encode semula
akan pecahkan signature.

## Idempotency

`idempotency_key` stabil: `mendap-order-{order_id}-{system}`. Ulangan kunci sama
memulangkan respons tersimpan di sebelah system — tiada grant berganda. Di sebelah
Mendap, jadual `provisioning_calls` unik pada `(order_id, system)`; status `done`
tak akan dipanggil semula.

Retry: hanya 5xx / 429 / connection error dilempar semula supaya queue cuba lagi
(3 kali, backoff 60s). 4xx (kunci salah, signature salah, emel tak sah) direkod
`failed` tanpa retry sebab retry takkan membaikinya.

## Setup

1. Set `PROVISIONING_API_KEY` + `PROVISIONING_SIGNING_SECRET` dalam `.env` — nilai
   MESTI sama dengan yang diset pada system sasaran.
2. `PROVISIONING_ENABLED=true`.
3. `php artisan migrate` lalu `php artisan config:cache`.
4. Pastikan queue worker berjalan (`QUEUE_CONNECTION=database`).
5. Dalam dashboard salespage → tab **Checkout** → *Akses automatik (provisioning)*,
   pilih system yang dibuka. `Tiada` = produk fizikal, tiada panggilan dibuat.

## Semak selepas jualan

```sql
select order_id, system, status, attempts, error from provisioning_calls order by id desc;
select order_id, type, status, error from whatsapp_logs where type = 'provision_access';
```
