# WhatsApp Sales Notifications — Implementation Plan (Phase 1)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** When a Mendap order is paid (BayarCash webhook) or shipped (EasyParcel), automatically send WhatsApp messages via the self-hosted WAHA (GOWS) instance — customer receipt, merchant alert, and shipping tracking.

**Architecture:** A small in-repo Composer path-package `amarbedaie/waha` (`packages/waha/`) wraps the WAHA HTTP API using Laravel's `Http` client. Mendap fires `OrderPaid` / `OrderShipped` events at the existing notification points (mirroring `Mailer::orderPaid`), and queued listeners send messages through the package. Every send is logged in `whatsapp_logs` and is idempotent (unique per `order_id`+`type`).

**Tech Stack:** Laravel 13.8, PHP 8.3, PHPUnit 12.5, `database` queue, WAHA (GOWS) at `https://waha.129-212-216-31.sslip.io` (session `amarbedaie`).

## Global Constraints

- PHP `^8.3`, Laravel `^13.8`. Match existing style: models use the `#[Fillable([...])]` attribute (not `$fillable`), migrations are anonymous-class `return new class extends Migration`.
- All customer-facing copy is **Malay**. Messages are transactional only (no bulk sends).
- Sending is **async** (queued listeners, `ShouldQueue`). The payment webhook must never block on WAHA.
- Feature-flagged: nothing sends unless `config('whatsapp.enabled')` is true.
- Secrets (`WAHA_API_KEY`) live in env only, never committed.
- Work on branch `feature/whatsapp-notifications`. Deployment is GitHub → Forge (never edit server files directly).
- Run tests from the repo root: `/Users/amar/laravel herd/lonjak-salespage`.

---

### Task 1: `amarbedaie/waha` package — WAHA client

**Files:**
- Create: `packages/waha/composer.json`
- Create: `packages/waha/src/WahaClient.php`
- Create: `packages/waha/src/WahaException.php`
- Create: `packages/waha/src/WahaServiceProvider.php`
- Create: `packages/waha/config/waha.php`
- Modify: `composer.json` (root — add path repository + require)
- Test: `tests/Unit/WahaClientTest.php`

**Interfaces:**
- Produces:
  - `Waha\WahaClient::__construct(string $baseUrl, string $apiKey, string $defaultSession = 'default', string $defaultCc = '60', int $timeout = 15)`
  - `Waha\WahaClient::chatId(string $phone, string $defaultCc = '60'): string` (static)
  - `Waha\WahaClient::sendText(string $chatId, string $text, ?string $session = null): array`
  - `Waha\WahaClient::defaultCc(): string`
  - `Waha\WahaException` (extends `\RuntimeException`)
  - Container binds `Waha\WahaClient` as a singleton from `config('waha.*')`.

- [ ] **Step 1: Create the package composer.json**

`packages/waha/composer.json`:
```json
{
    "name": "amarbedaie/waha",
    "description": "Minimal WAHA (WhatsApp HTTP API) client for Laravel apps.",
    "type": "library",
    "license": "MIT",
    "autoload": {
        "psr-4": { "Waha\\": "src/" }
    },
    "require": {
        "php": "^8.3",
        "illuminate/http": "^13.0",
        "illuminate/support": "^13.0"
    },
    "extra": {
        "laravel": {
            "providers": ["Waha\\WahaServiceProvider"]
        }
    },
    "minimum-stability": "stable"
}
```

- [ ] **Step 2: Create the exception**

`packages/waha/src/WahaException.php`:
```php
<?php

namespace Waha;

class WahaException extends \RuntimeException {}
```

- [ ] **Step 3: Create the client**

`packages/waha/src/WahaClient.php`:
```php
<?php

namespace Waha;

use Illuminate\Support\Facades\Http;

class WahaClient
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
        private string $defaultSession = 'default',
        private string $defaultCc = '60',
        private int $timeout = 15,
    ) {}

    /** Normalize a phone number to a WAHA chat id ("60123456789@c.us"). Returns '' when there are no digits. */
    public static function chatId(string $phone, string $defaultCc = '60'): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = $defaultCc.substr($digits, 1);
        }

        return $digits.'@c.us';
    }

    /** Send a text message. Throws WahaException on a non-2xx response. */
    public function sendText(string $chatId, string $text, ?string $session = null): array
    {
        $response = Http::withHeaders(['X-Api-Key' => $this->apiKey])
            ->timeout($this->timeout)
            ->acceptJson()
            ->post(rtrim($this->baseUrl, '/').'/api/sendText', [
                'session' => $session ?? $this->defaultSession,
                'chatId' => $chatId,
                'text' => $text,
            ]);

        if ($response->failed()) {
            throw new WahaException("WAHA sendText failed ({$response->status()}): ".$response->body());
        }

        return $response->json() ?? [];
    }

    public function defaultCc(): string
    {
        return $this->defaultCc;
    }
}
```

- [ ] **Step 4: Create the config file**

`packages/waha/config/waha.php`:
```php
<?php

return [
    'url' => env('WAHA_URL'),
    'api_key' => env('WAHA_API_KEY'),
    'session' => env('WAHA_SESSION', 'default'),
    'default_cc' => env('WAHA_DEFAULT_CC', '60'),
    'timeout' => (int) env('WAHA_TIMEOUT', 15),
];
```

- [ ] **Step 5: Create the service provider**

`packages/waha/src/WahaServiceProvider.php`:
```php
<?php

namespace Waha;

use Illuminate\Support\ServiceProvider;

class WahaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/waha.php', 'waha');

        $this->app->singleton(WahaClient::class, function ($app) {
            $c = $app['config']['waha'];

            return new WahaClient(
                (string) ($c['url'] ?? ''),
                (string) ($c['api_key'] ?? ''),
                (string) ($c['session'] ?? 'default'),
                (string) ($c['default_cc'] ?? '60'),
                (int) ($c['timeout'] ?? 15),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/waha.php' => config_path('waha.php')], 'waha-config');
    }
}
```

- [ ] **Step 6: Wire the path package into the root composer.json**

In `composer.json` (root), add a top-level `repositories` key (before `require`) and add the package to `require`:
```json
    "repositories": [
        { "type": "path", "url": "packages/waha", "options": { "symlink": true } }
    ],
    "require": {
        "php": "^8.3",
        "amarbedaie/waha": "*",
        "laravel/framework": "^13.8",
        "laravel/tinker": "^3.0",
        "livewire/livewire": "^4.3",
        "mallardduck/blade-lucide-icons": "^1.26",
        "webimpian/bayarcash-php-sdk": "^2.1"
    },
```

- [ ] **Step 7: Install dependencies + local env (first-time clone setup)**

Run from repo root:
```bash
composer install
cp -n .env.example .env
php artisan key:generate
composer update amarbedaie/waha --no-interaction
```
Expected: `amarbedaie/waha` installed (symlinked), `Waha\WahaServiceProvider` auto-discovered.

- [ ] **Step 8: Write the failing unit test**

`tests/Unit/WahaClientTest.php`:
```php
<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Waha\WahaClient;
use Waha\WahaException;

class WahaClientTest extends TestCase
{
    public function test_chat_id_normalizes_local_and_international_numbers(): void
    {
        $this->assertSame('60123456789@c.us', WahaClient::chatId('0123456789'));
        $this->assertSame('60123456789@c.us', WahaClient::chatId('+60 12-345 6789'));
        $this->assertSame('60123456789@c.us', WahaClient::chatId('60123456789'));
        $this->assertSame('60123456789@c.us', WahaClient::chatId('0060123456789'));
        $this->assertSame('', WahaClient::chatId('abc'));
    }

    public function test_send_text_posts_to_waha_and_returns_payload(): void
    {
        Http::fake(['*/api/sendText' => Http::response(['id' => 'true_123@c.us_ABC'], 201)]);

        $client = new WahaClient('https://waha.test', 'secret-key', 'amarbedaie');
        $result = $client->sendText('60123456789@c.us', 'Hai');

        $this->assertSame('true_123@c.us_ABC', $result['id']);
        Http::assertSent(fn ($request) => $request->url() === 'https://waha.test/api/sendText'
            && $request['session'] === 'amarbedaie'
            && $request['chatId'] === '60123456789@c.us'
            && $request['text'] === 'Hai'
            && $request->hasHeader('X-Api-Key', 'secret-key'));
    }

    public function test_send_text_throws_on_failure(): void
    {
        Http::fake(['*/api/sendText' => Http::response('unauthorized', 401)]);

        $this->expectException(WahaException::class);
        (new WahaClient('https://waha.test', 'bad', 'amarbedaie'))->sendText('60123456789@c.us', 'Hai');
    }
}
```

- [ ] **Step 9: Run the test to verify it fails**

Run: `php artisan test --filter=WahaClientTest`
Expected: FAIL (class `Waha\WahaClient` not found) — before Step 3 is autoloaded; after `composer update`, run again and it should PASS. If classes were created before this step, expect PASS.

- [ ] **Step 10: Run the test to verify it passes**

Run: `php artisan test --filter=WahaClientTest`
Expected: PASS (3 tests).

- [ ] **Step 11: Commit**

```bash
git add packages/waha composer.json composer.lock tests/Unit/WahaClientTest.php
git commit -m "feat(waha): add WAHA client package (chatId + sendText)"
```

---

### Task 2: Schema + config — `whatsapp_logs`, `users.notify_whatsapp`, `config/whatsapp.php`

**Files:**
- Create: `database/migrations/2026_07_04_000001_add_notify_whatsapp_to_users.php`
- Create: `database/migrations/2026_07_04_000002_create_whatsapp_logs_table.php`
- Create: `app/Models/WhatsAppLog.php`
- Create: `config/whatsapp.php`
- Modify: `app/Models/User.php` (add `notify_whatsapp` to `#[Fillable]`)
- Modify: `.env.example` (document new vars)
- Test: `tests/Feature/WhatsAppLogSchemaTest.php`

**Interfaces:**
- Produces:
  - `App\Models\WhatsAppLog` with fillable `['order_id','type','to','session','status','waha_message_id','error']`, `belongsTo(Order)`.
  - `config('whatsapp.enabled'): bool`, `config('whatsapp.admin_number'): ?string`.
  - `users.notify_whatsapp` nullable string(30).

- [ ] **Step 1: Migration — add `notify_whatsapp` to users**

`database/migrations/2026_07_04_000001_add_notify_whatsapp_to_users.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('notify_whatsapp', 30)->nullable()->after('ship_postcode');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notify_whatsapp');
        });
    }
};
```

- [ ] **Step 2: Migration — create `whatsapp_logs`**

`database/migrations/2026_07_04_000002_create_whatsapp_logs_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);            // paid_customer | paid_merchant | shipped
            $table->string('to', 40)->nullable();
            $table->string('session', 60)->nullable();
            $table->string('status', 20)->default('queued'); // queued | sent | failed
            $table->string('waha_message_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
```

- [ ] **Step 3: WhatsAppLog model**

`app/Models/WhatsAppLog.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'type', 'to', 'session', 'status', 'waha_message_id', 'error'])]
class WhatsAppLog extends Model
{
    protected $table = 'whatsapp_logs';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
```

- [ ] **Step 4: config/whatsapp.php**

`config/whatsapp.php`:
```php
<?php

return [
    // Master switch — nothing sends unless true.
    'enabled' => (bool) env('WHATSAPP_NOTIFICATIONS_ENABLED', false),

    // Fallback merchant number when a merchant has not set users.notify_whatsapp.
    'admin_number' => env('MENDAP_ADMIN_WHATSAPP'),
];
```

- [ ] **Step 5: Add `notify_whatsapp` to User fillable**

In `app/Models/User.php`, append `'notify_whatsapp'` to the `#[Fillable([...])]` array (after `'ship_postcode'`):
```php
#[Fillable(['name', 'business_name', 'phone', 'email', 'password', 'role', 'plan', 'ai_credits', 'status', 'bayarcash_pat', 'bayarcash_portal_key', 'bayarcash_api_secret', 'bayarcash_sandbox', 'bayarcash_active', 'easyparcel_api_key', 'easyparcel_sandbox', 'ship_name', 'ship_phone', 'ship_addr1', 'ship_addr2', 'ship_city', 'ship_state', 'ship_postcode', 'notify_whatsapp'])]
```

- [ ] **Step 6: Document env vars in .env.example**

Append to `.env.example`:
```
# --- WhatsApp (WAHA) notifications ---
WAHA_URL=https://waha.129-212-216-31.sslip.io
WAHA_API_KEY=
WAHA_SESSION=amarbedaie
WAHA_DEFAULT_CC=60
WHATSAPP_NOTIFICATIONS_ENABLED=false
MENDAP_ADMIN_WHATSAPP=
```

- [ ] **Step 7: Write the failing schema test**

`tests/Feature/WhatsAppLogSchemaTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\WhatsAppLog;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppLogSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_row_is_unique_per_order_and_type(): void
    {
        $order = Order::factory()->create();
        WhatsAppLog::create(['order_id' => $order->id, 'type' => 'paid_customer', 'status' => 'sent']);

        $this->expectException(QueryException::class);
        WhatsAppLog::create(['order_id' => $order->id, 'type' => 'paid_customer', 'status' => 'sent']);
    }
}
```
> Note: `Order::factory()` is created in Task 3. If running tasks strictly in order, move this test's run to after Task 3, or temporarily create the order via `Order::create([...])`. Recommended: implement Task 3 before running this test.

- [ ] **Step 8: Run migrations + test**

Run: `php artisan test --filter=WhatsAppLogSchemaTest`
Expected: PASS (unique constraint throws on the duplicate).

- [ ] **Step 9: Commit**

```bash
git add database/migrations app/Models/WhatsAppLog.php config/whatsapp.php app/Models/User.php .env.example tests/Feature/WhatsAppLogSchemaTest.php
git commit -m "feat(whatsapp): schema + config (whatsapp_logs, users.notify_whatsapp, config)"
```

---

### Task 3: Message templates + Order factory

**Files:**
- Create: `app/Support/WhatsAppTemplates.php`
- Create: `database/factories/OrderFactory.php`
- Modify: `app/Models/Order.php` (add `HasFactory`)
- Test: `tests/Unit/WhatsAppTemplatesTest.php`

**Interfaces:**
- Produces:
  - `App\Support\WhatsAppTemplates::paidCustomer(Order): string`
  - `App\Support\WhatsAppTemplates::paidMerchant(Order): string`
  - `App\Support\WhatsAppTemplates::shipped(Order): string`
  - `Database\Factories\OrderFactory` (default: `phone='0123456789'`, `total=49.00`, `product_name='Buku Tadabbur'`, `qty=1`, `state='Selangor'`, `user_id=User::factory()`).

- [ ] **Step 1: Add HasFactory to Order**

In `app/Models/Order.php`, add the trait import and use it:
```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
```
and inside the class body:
```php
    use HasFactory;
```

- [ ] **Step 2: Create OrderFactory**

`database/factories/OrderFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'salespage_id' => null,
            'customer' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '0123456789',
            'address' => fake()->address(),
            'state' => 'Selangor',
            'product_name' => 'Buku Tadabbur',
            'qty' => 1,
            'total' => 49.00,
            'status' => 'baru',
            'payment_status' => 'belum',
        ];
    }
}
```

- [ ] **Step 3: Create the templates class**

`app/Support/WhatsAppTemplates.php`:
```php
<?php

namespace App\Support;

use App\Models\Order;

class WhatsAppTemplates
{
    /** Best available store/brand name for sign-off. */
    public static function store(Order $order): string
    {
        return $order->salespage?->title
            ?: ($order->user?->business_name ?: ($order->user?->name ?: 'BeDaie'));
    }

    public static function paidCustomer(Order $order): string
    {
        $store = self::store($order);
        $total = number_format((float) $order->total, 2);

        return "Assalamualaikum {$order->customer} 🌙\n\n"
            ."Terima kasih! Pesanan #{$order->id} anda telah BERJAYA dibayar ✅\n\n"
            ."🛍️ {$order->product_name} × {$order->qty}\n"
            ."💰 RM{$total}\n\n"
            ."Pesanan anda sedang diproses. Butiran penghantaran akan menyusul, insyaAllah 🤍\n— {$store}";
    }

    public static function paidMerchant(Order $order): string
    {
        $total = number_format((float) $order->total, 2);

        return "🔔 ORDER BARU DIBAYAR ✅ #{$order->id}\n\n"
            ."👤 {$order->customer}\n"
            ."📞 {$order->phone}\n"
            ."🛍️ {$order->product_name} × {$order->qty}\n"
            ."💰 RM{$total}\n"
            ."📍 {$order->state}";
    }

    public static function shipped(Order $order): string
    {
        $store = self::store($order);

        return "📦 Pesanan #{$order->id} anda telah dihantar!\n\n"
            ."Kurier: {$order->courier}\n"
            ."No. Tracking: {$order->awb}\n"
            ."Jejak: {$order->tracking_url}\n\n"
            ."Terima kasih kerana membeli 🤍\n— {$store}";
    }
}
```

- [ ] **Step 4: Write the failing template test**

`tests/Unit/WhatsAppTemplatesTest.php`:
```php
<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Support\WhatsAppTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_customer_message_contains_order_details(): void
    {
        $order = Order::factory()->create(['customer' => 'Ali', 'product_name' => 'Buku Fatihah', 'qty' => 2, 'total' => 80]);

        $msg = WhatsAppTemplates::paidCustomer($order);

        $this->assertStringContainsString('Ali', $msg);
        $this->assertStringContainsString("#{$order->id}", $msg);
        $this->assertStringContainsString('Buku Fatihah × 2', $msg);
        $this->assertStringContainsString('RM80.00', $msg);
    }

    public function test_paid_merchant_message_contains_phone_and_total(): void
    {
        $order = Order::factory()->create(['phone' => '0191112222', 'total' => 49]);

        $msg = WhatsAppTemplates::paidMerchant($order);

        $this->assertStringContainsString('0191112222', $msg);
        $this->assertStringContainsString('RM49.00', $msg);
    }
}
```

- [ ] **Step 5: Run the test**

Run: `php artisan test --filter=WhatsAppTemplatesTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Support/WhatsAppTemplates.php database/factories/OrderFactory.php app/Models/Order.php tests/Unit/WhatsAppTemplatesTest.php
git commit -m "feat(whatsapp): Malay message templates + Order factory"
```

---

### Task 4: Paid notifications — event, queued listener, webhook hook

**Files:**
- Create: `app/Events/OrderPaid.php`
- Create: `app/Listeners/SendPaidWhatsAppNotifications.php`
- Modify: `app/Http/Controllers/PaymentController.php:26-33` (fire event in `callback()`)
- Test: `tests/Feature/OrderPaidWhatsAppTest.php`

**Interfaces:**
- Consumes: `Waha\WahaClient` (Task 1), `App\Models\WhatsAppLog` (Task 2), `App\Support\WhatsAppTemplates` (Task 3), `config('whatsapp.*')` (Task 2).
- Produces: `App\Events\OrderPaid` (public `Order $order`); listener auto-discovered via `handle(OrderPaid $event)`.

- [ ] **Step 1: Create the event**

`app/Events/OrderPaid.php`:
```php
<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;

class OrderPaid
{
    use Dispatchable;

    public function __construct(public Order $order) {}
}
```

- [ ] **Step 2: Create the queued listener**

`app/Listeners/SendPaidWhatsAppNotifications.php`:
```php
<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\WhatsAppLog;
use App\Support\WhatsAppTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;
use Waha\WahaClient;
use Waha\WahaException;

class SendPaidWhatsAppNotifications implements ShouldQueue
{
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(private WahaClient $waha) {}

    public function handle(OrderPaid $event): void
    {
        if (! config('whatsapp.enabled')) {
            return;
        }

        $order = $event->order->loadMissing('user', 'salespage');

        $this->send($order, 'paid_customer', $order->phone, WhatsAppTemplates::paidCustomer($order));

        $merchantNumber = $order->user?->notify_whatsapp ?: config('whatsapp.admin_number');
        $this->send($order, 'paid_merchant', $merchantNumber, WhatsAppTemplates::paidMerchant($order));
    }

    /** Idempotent single send: one row per (order, type); never re-sends an already-sent message. */
    private function send(Order $order, string $type, ?string $phone, string $text): void
    {
        $log = WhatsAppLog::firstOrNew(['order_id' => $order->id, 'type' => $type]);
        if ($log->status === 'sent') {
            return;
        }

        $chatId = $phone ? WahaClient::chatId($phone, $this->waha->defaultCc()) : '';
        if ($chatId === '') {
            $log->fill(['to' => $phone, 'status' => 'failed', 'error' => 'no_phone'])->save();

            return;
        }

        $log->fill(['to' => $chatId, 'session' => config('waha.session'), 'status' => 'queued'])->save();

        try {
            $res = $this->waha->sendText($chatId, $text);
            $log->update(['status' => 'sent', 'waha_message_id' => $res['id'] ?? null, 'error' => null]);
        } catch (WahaException $e) {
            $log->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 500)]);
            throw $e; // allow the queue to retry
        }
    }
}
```

- [ ] **Step 3: Fire the event from the payment webhook**

In `app/Http/Controllers/PaymentController.php`, method `callback()`, after `Mailer::orderPaid($order);` add the event. Result:
```php
    public function callback(Request $request)
    {
        $order = BayarcashGateway::applyCallback($request->all(), isWebhook: true);

        if ($order) {
            Mailer::orderPaid($order);
            event(new \App\Events\OrderPaid($order));
        }

        return response('OK', 200);
    }
```

- [ ] **Step 4: Write the failing feature test**

`tests/Feature/OrderPaidWhatsAppTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderPaidWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('whatsapp.enabled', true);
        config()->set('whatsapp.admin_number', '0198887777');
        config()->set('waha.url', 'https://waha.test');
        config()->set('waha.api_key', 'k');
        config()->set('waha.session', 'amarbedaie');
        config()->set('waha.default_cc', '60');
    }

    public function test_paid_order_sends_customer_and_merchant_whatsapp(): void
    {
        Http::fake(['*/api/sendText' => Http::response(['id' => 'wamid.X'], 201)]);
        $merchant = User::factory()->create(['notify_whatsapp' => '0191112222']);
        $order = Order::factory()->for($merchant, 'user')->create(['phone' => '0123456789']);

        event(new OrderPaid($order));

        Http::assertSentCount(2);
        $this->assertDatabaseHas('whatsapp_logs', ['order_id' => $order->id, 'type' => 'paid_customer', 'status' => 'sent', 'to' => '60123456789@c.us']);
        $this->assertDatabaseHas('whatsapp_logs', ['order_id' => $order->id, 'type' => 'paid_merchant', 'status' => 'sent', 'to' => '60191112222@c.us']);
    }

    public function test_it_is_idempotent_across_repeated_events(): void
    {
        Http::fake(['*/api/sendText' => Http::response(['id' => 'wamid.X'], 201)]);
        $order = Order::factory()->create(['phone' => '0123456789']);

        event(new OrderPaid($order));
        event(new OrderPaid($order));

        Http::assertSentCount(2); // customer + merchant once, not four
    }

    public function test_missing_customer_phone_is_logged_failed(): void
    {
        Http::fake(['*/api/sendText' => Http::response(['id' => 'x'], 201)]);
        config()->set('whatsapp.admin_number', null);
        $merchant = User::factory()->create(['notify_whatsapp' => null]);
        $order = Order::factory()->for($merchant, 'user')->create(['phone' => '']);

        event(new OrderPaid($order));

        $this->assertDatabaseHas('whatsapp_logs', ['order_id' => $order->id, 'type' => 'paid_customer', 'status' => 'failed', 'error' => 'no_phone']);
    }

    public function test_disabled_flag_sends_nothing(): void
    {
        config()->set('whatsapp.enabled', false);
        Http::fake();
        $order = Order::factory()->create(['phone' => '0123456789']);

        event(new OrderPaid($order));

        Http::assertNothingSent();
    }
}
```

- [ ] **Step 5: Run the test to verify it fails, then passes**

Run: `php artisan test --filter=OrderPaidWhatsAppTest`
Expected first run (before listener exists): FAIL. After Steps 1–3: PASS (4 tests).
> Queue is `sync` in tests (`phpunit.xml`), so the queued listener runs inline — no `Queue::fake()` needed. Listener auto-discovery is on by default in Laravel 11+. If the listener does not fire, register it explicitly in `app/Providers/AppServiceProvider.php@boot`: `\Illuminate\Support\Facades\Event::listen(\App\Events\OrderPaid::class, \App\Listeners\SendPaidWhatsAppNotifications::class);`

- [ ] **Step 6: Commit**

```bash
git add app/Events/OrderPaid.php app/Listeners/SendPaidWhatsAppNotifications.php app/Http/Controllers/PaymentController.php tests/Feature/OrderPaidWhatsAppTest.php
git commit -m "feat(whatsapp): paid-order notifications (customer receipt + merchant alert)"
```

---

### Task 5: Shipping notification — event, queued listener, hook

**Files:**
- Create: `app/Events/OrderShipped.php`
- Create: `app/Listeners/SendShippedWhatsAppNotification.php`
- Modify: `app/Http/Controllers/ShippingController.php:100-111` (fire event after AWB saved)
- Test: `tests/Feature/OrderShippedWhatsAppTest.php`

**Interfaces:**
- Consumes: `Waha\WahaClient`, `App\Models\WhatsAppLog`, `App\Support\WhatsAppTemplates`, `config('whatsapp.*')`.
- Produces: `App\Events\OrderShipped` (public `Order $order`).

- [ ] **Step 1: Create the event**

`app/Events/OrderShipped.php`:
```php
<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;

class OrderShipped
{
    use Dispatchable;

    public function __construct(public Order $order) {}
}
```

- [ ] **Step 2: Create the queued listener**

`app/Listeners/SendShippedWhatsAppNotification.php`:
```php
<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Models\WhatsAppLog;
use App\Support\WhatsAppTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;
use Waha\WahaClient;
use Waha\WahaException;

class SendShippedWhatsAppNotification implements ShouldQueue
{
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(private WahaClient $waha) {}

    public function handle(OrderShipped $event): void
    {
        if (! config('whatsapp.enabled')) {
            return;
        }

        $order = $event->order->loadMissing('user', 'salespage');

        $log = WhatsAppLog::firstOrNew(['order_id' => $order->id, 'type' => 'shipped']);
        if ($log->status === 'sent') {
            return;
        }

        $chatId = $order->phone ? WahaClient::chatId($order->phone, $this->waha->defaultCc()) : '';
        if ($chatId === '') {
            $log->fill(['to' => $order->phone, 'status' => 'failed', 'error' => 'no_phone'])->save();

            return;
        }

        $log->fill(['to' => $chatId, 'session' => config('waha.session'), 'status' => 'queued'])->save();

        try {
            $res = $this->waha->sendText($chatId, WhatsAppTemplates::shipped($order));
            $log->update(['status' => 'sent', 'waha_message_id' => $res['id'] ?? null, 'error' => null]);
        } catch (WahaException $e) {
            $log->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 500)]);
            throw $e;
        }
    }
}
```

- [ ] **Step 3: Fire the event after a shipment is booked**

In `app/Http/Controllers/ShippingController.php`, method `book()`, after the `$order->update([...])` block (the one setting `awb`/`tracking_url`) and before `return back()...`, add:
```php
        event(new \App\Events\OrderShipped($order));
```

- [ ] **Step 4: Write the failing feature test**

`tests/Feature/OrderShippedWhatsAppTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Events\OrderShipped;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderShippedWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('whatsapp.enabled', true);
        config()->set('waha.url', 'https://waha.test');
        config()->set('waha.api_key', 'k');
        config()->set('waha.session', 'amarbedaie');
        config()->set('waha.default_cc', '60');
    }

    public function test_shipped_order_sends_tracking_to_customer(): void
    {
        Http::fake(['*/api/sendText' => Http::response(['id' => 'wamid.SHIP'], 201)]);
        $order = Order::factory()->create([
            'phone' => '0123456789',
            'courier' => 'J&T',
            'awb' => 'JT123456',
            'tracking_url' => 'https://track.test/JT123456',
        ]);

        event(new OrderShipped($order));

        Http::assertSentCount(1);
        Http::assertSent(fn ($r) => str_contains($r['text'], 'JT123456') && $r['chatId'] === '60123456789@c.us');
        $this->assertDatabaseHas('whatsapp_logs', ['order_id' => $order->id, 'type' => 'shipped', 'status' => 'sent']);
    }
}
```

- [ ] **Step 5: Run the test**

Run: `php artisan test --filter=OrderShippedWhatsAppTest`
Expected: PASS (1 test).

- [ ] **Step 6: Run the full suite**

Run: `php artisan test`
Expected: all green (WahaClient, WhatsAppLogSchema, WhatsAppTemplates, OrderPaidWhatsApp, OrderShippedWhatsApp, ExampleTest).

- [ ] **Step 7: Commit**

```bash
git add app/Events/OrderShipped.php app/Listeners/SendShippedWhatsAppNotification.php app/Http/Controllers/ShippingController.php tests/Feature/OrderShippedWhatsAppTest.php
git commit -m "feat(whatsapp): shipping tracking notification"
```

---

### Task 6: Deploy — env, queue worker, migrate, enable, verify

**Files:** none (infra/ops). Executed against Forge server `serverdeveloperbedaie` (`129.212.216.31`), site `mendap.com`.

- [ ] **Step 1: Push the branch and open a PR (do not merge to main without user OK)**

```bash
git push -u origin feature/whatsapp-notifications
gh pr create --fill --base main --head feature/whatsapp-notifications
```

- [ ] **Step 2: Set env on the Forge site** (Forge UI → mendap.com → Environment, or via SSH edit of `~/mendap.com/.env`). Retrieve the real `WAHA_API_KEY` from `~/waha/.env` on the server. Add:
```
WAHA_URL=https://waha.129-212-216-31.sslip.io
WAHA_API_KEY=<value from ~/waha/.env WAHA_API_KEY>
WAHA_SESSION=amarbedaie
WAHA_DEFAULT_CC=60
WHATSAPP_NOTIFICATIONS_ENABLED=false
MENDAP_ADMIN_WHATSAPP=<merchant/owner WhatsApp number, e.g. 60111998xxxx>
```

- [ ] **Step 3: Merge PR → Forge auto-deploys.** Ensure the deploy script runs `php artisan migrate --force` (Forge default includes it). Verify `whatsapp_logs` + `users.notify_whatsapp` exist:
```bash
ssh -i ~/.ssh/waha_forge forge@129.212.216.31 "cd ~/mendap.com && php artisan migrate --force && php artisan tinker --execute=\"echo Schema::hasTable('whatsapp_logs')?'logs_ok':'MISSING';\""
```
Expected: `logs_ok`.

- [ ] **Step 4: Create a Forge Daemon for the queue worker** (Forge UI → Server → Daemons, or MCP). Command / directory / user:
```
Command:   php8.3 /home/forge/mendap.com/artisan queue:work --queue=default --sleep=3 --tries=3 --max-time=3600
Directory: /home/forge/mendap.com
User:      forge
```
Start it. (After each deploy, run `php artisan queue:restart` so the worker picks up new code — add to the deploy script.)

- [ ] **Step 5: Manual end-to-end verification (flag still OFF → then ON for one test)**

Temporarily set `WHATSAPP_NOTIFICATIONS_ENABLED=true`, `php artisan config:clear`, then in tinker on the server dispatch a paid event for a real test order whose `phone` is your own number:
```bash
ssh -i ~/.ssh/waha_forge forge@129.212.216.31 "cd ~/mendap.com && php artisan tinker --execute=\"\\\$o=App\\Models\\Order::factory()->create(['phone'=>'60111998XXXX','payment_status'=>'dibayar']); event(new App\\Events\\OrderPaid(\\\$o)); echo 'dispatched';\""
```
Expected: WhatsApp receipt arrives on the test number within seconds; `whatsapp_logs` row `status=sent`. (Requires the WAHA session `amarbedaie` to be WORKING.)

- [ ] **Step 6: Enable for real + confirm a genuine paid order**

Keep `WHATSAPP_NOTIFICATIONS_ENABLED=true`. Place a real low-value order through a salespage, pay, and confirm the customer + merchant messages arrive. Rollback at any time = set the flag `false` + `php artisan config:clear` (no data loss).

- [ ] **Step 7: Commit deploy notes (if the deploy script changed)**

```bash
git add -A && git commit -m "chore(deploy): queue worker + config:clear on deploy for WhatsApp notifications"
```

---

## Self-Review

**Spec coverage:**
- waha-php helper → Task 1 ✅
- OrderPaid/OrderShipped events fired at `applyCallback` path + `ShippingController@book` → Tasks 4/5 (event fired in `PaymentController@callback`, the same authoritative point where `Mailer::orderPaid` already runs) ✅
- Queued listeners: customer receipt + merchant alert + tracking → Tasks 4/5 ✅
- `whatsapp_logs` + `users.notify_whatsapp` migrations → Task 2 ✅
- Malay templates → Task 3 ✅
- Feature flag → Task 2 (config) + enforced in listeners (Tasks 4/5) ✅
- Idempotency (unique order_id+type, skip when sent) → Tasks 2/4/5 ✅
- Forge queue worker daemon → Task 6 ✅
- Unit + feature tests → every task ✅

**Refinements from spec (CTO calls, noted):**
1. `waha-php` ships as an **in-repo path package** (`packages/waha/`) rather than a separate GitHub repo — simpler deployment (no private-package auth), same isolation; extract to a standalone repo when the Phase-2 Hub needs it.
2. Client uses Laravel's `Http` client (not framework-agnostic Guzzle) — all consumers are Laravel apps; makes tests trivial via `Http::fake()`.
3. `sendImage` deferred (YAGNI — Phase 1 messages are all text).

**Placeholder scan:** none — all code and commands are concrete. (`<value from ~/waha/.env>` and `60111998XXXX` in Task 6 are intentional operator inputs, not code placeholders.)

**Type consistency:** `WahaClient::chatId`, `sendText`, `defaultCc` signatures identical across Tasks 1/4/5. `WhatsAppLog` fillable keys match every `create`/`fill` call. Event class names (`OrderPaid`, `OrderShipped`) consistent between events, listeners, hooks, and tests.
