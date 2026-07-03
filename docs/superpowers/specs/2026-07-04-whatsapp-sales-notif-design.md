# WhatsApp Sales Notifications — Design Spec

**Project:** BeDaie / Mendap · **Phase:** 1 of 3 · **Date:** 2026-07-04
**Scope:** `#0` `waha-php` Helper package + `#1` Mendap sales auto-notifications
**Depends on:** self-hosted WAHA (GOWS) at `https://waha.129-212-216-31.sslip.io`, session `amarbedaie`

---

## Ringkasan (Malay)

Bila order Mendap **dibayar** (callback BayarCash) atau **dihantar** (EasyParcel), sistem auto-hantar WhatsApp melalui WAHA: **resit ke customer**, **alert ke merchant**, dan **link tracking** bila dihantar. Dibina atas satu package kecil `waha-php` (boleh guna semula untuk bot & broadcaster nanti). Hantaran dibuat secara **async (queue)** supaya callback bayaran tak pernah tersekat. Semua hantaran dilog + idempotent (tak double-send).

---

## 1. Goals

- Auto WhatsApp **receipt** to customer on successful payment.
- Auto WhatsApp **alert** to merchant on each paid order.
- Auto WhatsApp **tracking link** to customer when shipment is booked.
- A reusable, framework-agnostic **`waha-php` client** (foundation for Phase 2/3).
- Resilient: sending never blocks or breaks the payment callback.

## 2. Non-goals (Phase 1)

- Per-merchant WhatsApp sessions / QR linking → **Phase 2**.
- Inbound messages / AI auto-reply → **Phase 2** (separate WhatsApp Hub app).
- Scheduled broadcasts → **Phase 3**.
- Rich media receipts (PDF invoice), delivery-status webhooks from WAHA.

## 3. Architecture

```
Customer → Salespage checkout → BayarCash
                                    │ (server callback)
                    BayarcashGateway::applyCallback()  ── status SUCCESS
                                    │  dispatch event OrderPaid($order)
                                    ▼
              [queued listener] SendPaidNotifications
                                    │  uses waha-php Helper
                    ┌───────────────┴───────────────┐
                    ▼                                ▼
       📩 Customer receipt (order.phone)   🔔 Merchant alert (merchant.notify_whatsapp)
                                    │
                    WAHA (GOWS) session `amarbedaie` → WhatsApp

ShippingController@book() ── AWB/tracking set → event OrderShipped
   → [queued listener] SendShippedNotification → 📦 tracking link to customer
```

Two units, each independently testable:

- **`#0` waha-php** — HTTP client for WAHA. No Mendap knowledge.
- **`#1` Mendap integration** — events, queued listeners, message templates, logging. Knows Mendap; talks to WAHA only through the Helper.

## 4. `#0` waha-php package

New repo: **`amarbedaie/waha-php`**, installed into Mendap via Composer (VCS/path). Framework-agnostic core + thin Laravel bridge.

**Core**
- `Waha\WahaClient`
  - `sendText(string $chatId, string $text, ?string $session = null): array`
  - `sendImage(string $chatId, string $url, ?string $caption = null, ?string $session = null): array`
  - `sessionStatus(?string $session = null): array`
  - `static chatId(string $phone, string $defaultCc = '60'): string` — digits-only, local `0…` → `60…`, returns `<intl>@c.us`
- Transport: PSR-18 / Guzzle. Sends `X-Api-Key`. Connect+read timeouts. Throws `Waha\WahaException` on non-2xx.
- Config object: `baseUrl`, `apiKey`, `defaultSession`, `defaultCountryCode`, `timeout`.

**Laravel bridge**
- `WahaServiceProvider` (auto-discovered), `config/waha.php`, facade `Waha`.
- Env: `WAHA_URL`, `WAHA_API_KEY`, `WAHA_SESSION`, `WAHA_DEFAULT_CC=60`.

## 5. `#1` Mendap integration

**Order data available:** `customer, phone, email, product_name, qty, total, state, address, courier, awb, tracking_url, status, payment_status, salespage_id, user_id (merchant)`.

**Hooks (minimal, additive)**
1. `App\Services\BayarcashGateway::applyCallback()` — after `payment_status='dibayar'` is set on SUCCESS, `event(new OrderPaid($order))`.
2. `App\Http\Controllers\ShippingController@book()` — after AWB/tracking saved, `event(new OrderShipped($order))`.

**Events & queued listeners** (`ShouldQueue`, `database` connection)
- `OrderPaid` → `SendPaidNotifications` (customer receipt + merchant alert).
- `OrderShipped` → `SendShippedNotification` (tracking link).

**Messages** — `App\Support\WhatsAppTemplates`, Malay, editable via `lang/ms/whatsapp.php`. Drafts:

- *Customer paid:*
  `Assalamualaikum {customer} 🌙 Terima kasih! Pesanan #{id} anda BERJAYA dibayar ✅\n🛍️ {product_name} × {qty} · 💰 RM{total}\nSedang diproses. Butiran penghantaran menyusul, insyaAllah 🤍 — {store}`
- *Merchant alert:*
  `🔔 ORDER BARU DIBAYAR ✅ #{id}\n👤 {customer} · 📞 {phone}\n🛍️ {product_name} ×{qty} · 💰 RM{total} · 📍 {state}`
- *Shipped:*
  `📦 Pesanan #{id} anda telah dihantar!\nKurier: {courier} · No.: {awb}\nJejak: {tracking_url}\nTerima kasih 🤍 — {store}`

**Sender session:** single configured `WAHA_SESSION` (`amarbedaie`) for MVP; session param is plumbed end-to-end so Phase 2 can switch to per-merchant with a config change only.

**Merchant notify target:** new nullable `users.notify_whatsapp`; fallback to env `MENDAP_ADMIN_WHATSAPP`. One field added to the existing Payment settings page.

**Logging & idempotency**
- Table `whatsapp_logs`: `id, order_id, type[paid_customer|paid_merchant|shipped], to, session, status[queued|sent|failed], waha_message_id, error, created_at, updated_at`.
- Unique `(order_id, type)` → BayarCash sending both return-URL + webhook callbacks never double-sends.
- Job `tries=3`, exponential backoff; final failure → `status=failed` (re-sendable from admin later).

## 6. Reliability / error handling

- Queue: `database` (already configured). New Forge daemon: `php artisan queue:work --queue=default --sleep=3 --tries=3 --max-time=3600`.
- WAHA down/slow → listener retries; callback path only dispatches an event then returns 200 to BayarCash.
- Missing/invalid phone → skip customer send, log `status=failed, error=no_phone` (merchant alert still attempted).
- Feature flag `WHATSAPP_NOTIFICATIONS_ENABLED` (default off until verified live).

## 7. Security / anti-ban

- `WAHA_API_KEY` in env, never committed.
- **Transactional only** — recipient just paid/ordered = implicit consent. No bulk/cold sends in this phase.
- Sending from a personal number carries ban risk; mitigated by low volume + transactional-only. Documented for the user.

## 8. Testing

- **waha-php:** unit tests (mocked HTTP) for `chatId()` normalization (MY `01…`, intl, `+`, spaces) and send payloads.
- **Mendap (feature):** factory order → `event(OrderPaid)` → assert listeners queued, assert Waha fake received expected payloads, assert `whatsapp_logs` rows. Idempotency (fire twice → one send). Missing phone → customer skipped, merchant still sent. Feature-flag off → nothing sent.
- **Manual:** real test order → simulate BayarCash SUCCESS callback → verify WhatsApp received on a test number; then a real end-to-end paid order.

## 9. Rollout

1. Publish `waha-php`; `composer require` in Mendap.
2. Migrate (`whatsapp_logs`, `users.notify_whatsapp`).
3. Set env on Forge (`WAHA_*`, `MENDAP_ADMIN_WHATSAPP`, flag off).
4. Add + start queue-worker daemon.
5. Enable flag for a test salespage → verify → enable globally.
6. Rollback = flip flag off (no data loss).

## 10. Deliverables checklist

- [ ] `amarbedaie/waha-php` repo + package (client, Laravel bridge, tests)
- [ ] Mendap: `composer require`, `config/waha.php`
- [ ] Events `OrderPaid`, `OrderShipped`; hooks in `BayarcashGateway` + `ShippingController`
- [ ] Listeners `SendPaidNotifications`, `SendShippedNotification`
- [ ] `WhatsAppTemplates` + `lang/ms/whatsapp.php`
- [ ] Migrations: `whatsapp_logs`, `users.notify_whatsapp`
- [ ] Payment-settings field (merchant notify number) + feature flag
- [ ] Forge queue-worker daemon
- [ ] Unit + feature tests; manual end-to-end verification
