# Day 3 Implementation Plan — Trading & Investors Modules

> **Sub-skill banner:** This plan is written to be executed with the
> `superpowers:executing-plans` + `superpowers:test-driven-development` skills.
> Every endpoint task follows **RED → GREEN → REFACTOR**: write the failing
> feature test first, run it, then implement until green. Each task ends with a
> git commit. Do **not** batch commits.

---

## Goal

Ship the **Trading** module (share offerings, CMF approval workflow, order book,
dividends) and the **Investors** module (investor profiles, KYC, investment
pledges, portfolio) for the Galerie virtuelle de l'artisanat du Cameroun Platform.

By end of Day 3:

- Companies can create share offerings and submit them to the CMF (Commission
  des Marchés Financiers) for approval.
- CMF reviewers can approve/reject offerings.
- Investors can place buy/sell orders against an open offering's order book.
- Investors can create & manage a profile, submit KYC, and pledge to offerings.
- Investors get a consolidated portfolio summary.

## Architecture

- **Laravel 13 modular monolith.** Each module lives under `app/Modules/<Name>/`
  with `Models/`, `Controllers/`, `Requests/`, `Resources/`, `Services/`,
  `Routes/`, `Providers/`.
- **Routes** are loaded by each module's `ServiceProvider::boot()` via
  `loadRoutesFrom()`. All routes are prefixed `api/v1` and use the `api`
  middleware group; the guard is `api` (Laravel Passport).
- **Authorization**: OAuth scopes via `scopes:<scope>` middleware + Spatie roles
  via `role:<role>` middleware. Owner/membership checks live in services.
- **Search**: Laravel Scout + Meilisearch (`Searchable` trait on `ShareOffering`).
- **Money**: all amounts are stored as **integer minor units (XAF has no minor
  unit, so these are whole XAF)** — `bigInteger` columns. Prices are
  `decimal(15,2)`. Never use floats for money math in services.
- **IDs**: `share_offerings`, `orders`, `trades`, `investment_pledges` use **UUID
  PKs** (`HasUuids`). `offering_documents`, `investor_profiles`,
  `kyc_applications`, `kyc_documents`, `portfolios` use **auto-increment** PKs.
- **`investor_id` everywhere = `users.id` (a UUID)** — confirmed by the FK in
  the migrations (`->references('id')->on('users')`). There is no separate
  investor table for the FK; `investor_profiles.user_id` is a 1:1 satellite.

## Tech stack

PHP 8.3, Laravel 13, Laravel Passport, Laravel Scout + Meilisearch, Spatie
Permission, Pest/PHPUnit feature tests, MySQL.

## OAuth scopes used (defined Day 1)

| Scope | Meaning |
|-------|---------|
| `companies:write` | create/manage offerings (company side) |
| `investor:profile` | read/write own investor profile + KYC |
| `investor:pledge` | place orders / make pledges |
| `investor:portfolio` | read own portfolio & pledges |

## Roles used

`super_admin`, `cmf_reviewer` (approves offerings + KYC compliance),
`company_owner`, `company_member`, `investor`, `public`.

> **Note on `admin:compliance`:** the task brief lists an `admin:compliance`
> role for KYC. Day 1 seeded **`cmf_reviewer`** as the compliance/regulatory
> role. To stay consistent with the seeded role set this plan guards KYC admin
> endpoints with `role:cmf_reviewer,super_admin`. If `admin:compliance` is later
> added as a *scope*, add `scopes:admin:compliance` alongside the role guard.

---

## Verified schema (read from migrations — do not trust memory)

**`2026_06_24_080300_create_trading_module_tables.php`:**

- `share_offerings` (UUID): `company_id`, `title_fr`, `title_en`, `summary_fr`,
  `summary_en`, `instrument_type` enum(`ordinary_shares`,`preference_shares`,
  `bonds`,`convertible_notes`), `status` enum(`draft`,`pending_cmf`,
  `cmf_approved`,`open`,`paused`,`closed`,`cancelled`,`completed`),
  `target_amount`, `minimum_amount`, `maximum_amount`, `amount_raised`,
  `share_price` dec(15,2), `total_shares`, `shares_sold`, `equity_offered`
  dec(5,2), `min_investment` int default 10000, `max_investment`, `open_date`,
  `close_date`, `currency` default `XAF`, `platform_fee_pct` dec(4,2) default
  2.50, `cmf_reviewer_id`, `cmf_approved_at`, `cmf_notes`, timestamps,
  softDeletes.
- `offering_documents` (id): `offering_id`(uuid), `type`
  enum(`prospectus`,`financial_statement`,`business_plan`,
  `investor_presentation`,`term_sheet`,`other`), `title_fr`, `title_en`,
  `file_path`, `visibility` enum(`public`,`investors_only`,`cmf_only`) default
  `investors_only`.
- `orders` (UUID): `offering_id`, `investor_id`, `type` enum(`buy`,`sell`),
  `status` enum(`pending`,`processing`,`filled`,`partially_filled`,`cancelled`,
  `expired`,`refunded`), `quantity`, `unit_price` dec(15,2), `total_amount`,
  `filled_quantity` default 0, `payment_reference`, `payment_method`
  enum(`mtn_momo`,`orange_money`,`bank_transfer`), `expires_at`.
- `trades` (UUID): `offering_id`, `buy_order_id`, `sell_order_id`(null),
  `buyer_id`, `seller_id`(null), `quantity`, `price` dec(15,2), `total_amount`,
  `platform_fee` dec(15,2), `vat_amount` dec(15,2), `settlement_status`
  enum(`pending`,`settled`,`failed`), `settled_at`.
- `dividend_declarations` (id): `company_id`(uuid), `offering_id`(uuid,null),
  `amount_per_share` dec(15,4), `record_date`, `payment_date`, `status`
  enum(`declared`,`processing`,`paid`,`cancelled`), `total_payout`.
- (`cmf_approvals` id table also exists: `offering_id`, `reviewer_id`,
  `decision` enum(`approved`,`rejected`,`more_info_required`), `notes_fr`,
  `notes_en`, `required_docs` json, `decided_at`.)

**`2026_06_24_080400_create_investors_module_tables.php`:**

- `investor_profiles` (id): `user_id`(uuid,unique), `investor_type`
  enum(`individual`,`institutional`), `accreditation_level`
  enum(`retail`,`qualified`,`institutional`), `national_id`, `id_type`, `dob`,
  `nationality`, `occupation`, `employer`, `annual_income`, `net_worth`,
  `risk_tolerance` enum(`conservative`,`moderate`,`aggressive`), `is_pep` bool,
  `is_sanctioned` bool, `bank_name`, `bank_account`, `bank_rib`.
- `kyc_applications` (id): `user_id`(uuid), `tier`
  enum(`basic`,`standard`,`enhanced`), `status`
  enum(`draft`,`submitted`,`in_review`,`approved`,`rejected`,`expired`),
  `reviewed_by`(uuid,null), `rejection_reason_fr`, `rejection_reason_en`,
  `submitted_at`, `reviewed_at`, `expires_at`.
- `kyc_documents` (id): `kyc_application_id`(fk), `type`
  enum(`national_id_front`,`national_id_back`,`passport`,`selfie`,
  `proof_of_address`,`bank_statement`,`other`), `file_path`, `original_name`,
  `is_accepted` bool(null), `rejection_reason`.
- `investment_pledges` (UUID): `investor_id`(uuid→users), `offering_id`(uuid),
  `amount`, `shares_requested`(null), `status`
  enum(`pending`,`payment_initiated`,`payment_received`,`order_created`,
  `cancelled`,`expired`), `payment_method`, `payment_reference`,
  `payment_initiated_at`, `payment_confirmed_at`, `expires_at`.
- `portfolios` (id): `investor_id`(uuid,unique), `total_invested`,
  `current_value`, `total_dividends_received`, `companies_count`.
- (`portfolio_transactions`, `watchlists`, `risk_assessments` also exist.)

---

# Task D3-1: Trading Module Models

**Goal:** Eloquent models + services + factory for the Trading module. No HTTP
yet. This task has no feature test (models are exercised by D3-2/D3-3 tests); it
ends with a smoke `tinker`-style assertion in a unit test.

### File: `app/Modules/Trading/Models/ShareOffering.php`

```php
<?php

namespace App\Modules\Trading\Models;

use App\Modules\Directory\Models\Company;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class ShareOffering extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use Searchable;

    protected $keyType = 'string';
    public $incrementing = false;

    /** Full status workflow. */
    public const STATUS_DRAFT        = 'draft';
    public const STATUS_PENDING_CMF  = 'pending_cmf';
    public const STATUS_CMF_APPROVED = 'cmf_approved';
    public const STATUS_OPEN         = 'open';
    public const STATUS_PAUSED       = 'paused';
    public const STATUS_CLOSED       = 'closed';
    public const STATUS_CANCELLED    = 'cancelled';
    public const STATUS_COMPLETED    = 'completed';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PENDING_CMF,
        self::STATUS_CMF_APPROVED,
        self::STATUS_OPEN,
        self::STATUS_PAUSED,
        self::STATUS_CLOSED,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
    ];

    /** Statuses the public may see in listings/show. */
    public const PUBLIC_STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_PAUSED,
        self::STATUS_CLOSED,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'company_id',
        'title_fr',
        'title_en',
        'summary_fr',
        'summary_en',
        'instrument_type',
        'status',
        'target_amount',
        'minimum_amount',
        'maximum_amount',
        'amount_raised',
        'share_price',
        'total_shares',
        'shares_sold',
        'equity_offered',
        'min_investment',
        'max_investment',
        'open_date',
        'close_date',
        'currency',
        'platform_fee_pct',
        'cmf_reviewer_id',
        'cmf_approved_at',
        'cmf_notes',
    ];

    protected $casts = [
        'target_amount'    => 'integer',
        'minimum_amount'   => 'integer',
        'maximum_amount'   => 'integer',
        'amount_raised'    => 'integer',
        'share_price'      => 'decimal:2',
        'total_shares'     => 'integer',
        'shares_sold'      => 'integer',
        'equity_offered'   => 'decimal:2',
        'min_investment'   => 'integer',
        'max_investment'   => 'integer',
        'platform_fee_pct' => 'decimal:2',
        'open_date'        => 'date',
        'close_date'       => 'date',
        'cmf_approved_at'  => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    protected static function newFactory(): \Database\Factories\ShareOfferingFactory
    {
        return \Database\Factories\ShareOfferingFactory::new();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(OfferingDocument::class, 'offering_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'offering_id');
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class, 'offering_id');
    }

    public function pledges(): HasMany
    {
        return $this->hasMany(
            \App\Modules\Investors\Models\InvestmentPledge::class,
            'offering_id'
        );
    }

    /** Is the offering accepting orders/pledges right now? */
    public function isOpenForInvestment(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function searchableAs(): string
    {
        return 'share_offerings';
    }

    public function toSearchableArray(): array
    {
        return [
            'id'              => (string) $this->id,
            'company_id'      => (string) $this->company_id,
            'title_fr'        => $this->title_fr,
            'title_en'        => $this->title_en,
            'summary_fr'      => $this->summary_fr,
            'summary_en'      => $this->summary_en,
            'instrument_type' => $this->instrument_type,
            'status'          => $this->status,
            'target_amount'   => (int) $this->target_amount,
            'amount_raised'   => (int) $this->amount_raised,
            'share_price'     => (float) $this->share_price,
            'created_at'      => optional($this->created_at)->timestamp,
        ];
    }

    /** Only publicly visible offerings should hit the search index. */
    public function shouldBeSearchable(): bool
    {
        return in_array($this->status, self::PUBLIC_STATUSES, true);
    }
}
```

### File: `app/Modules/Trading/Models/OfferingDocument.php`

```php
<?php

namespace App\Modules\Trading\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferingDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'offering_id',
        'type',
        'title_fr',
        'title_en',
        'file_path',
        'visibility',
    ];

    public function offering(): BelongsTo
    {
        return $this->belongsTo(ShareOffering::class, 'offering_id');
    }
}
```

### File: `app/Modules/Trading/Models/Order.php`

```php
<?php

namespace App\Modules\Trading\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    public const TYPE_BUY  = 'buy';
    public const TYPE_SELL = 'sell';

    public const STATUS_PENDING          = 'pending';
    public const STATUS_PROCESSING       = 'processing';
    public const STATUS_FILLED           = 'filled';
    public const STATUS_PARTIALLY_FILLED = 'partially_filled';
    public const STATUS_CANCELLED        = 'cancelled';
    public const STATUS_EXPIRED          = 'expired';
    public const STATUS_REFUNDED         = 'refunded';

    /** Orders that still sit on the open book. */
    public const OPEN_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_PARTIALLY_FILLED,
    ];

    protected $fillable = [
        'offering_id',
        'investor_id',
        'type',
        'status',
        'quantity',
        'unit_price',
        'total_amount',
        'filled_quantity',
        'payment_reference',
        'payment_method',
        'expires_at',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'unit_price'      => 'decimal:2',
        'total_amount'    => 'integer',
        'filled_quantity' => 'integer',
        'expires_at'      => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function offering(): BelongsTo
    {
        return $this->belongsTo(ShareOffering::class, 'offering_id');
    }

    public function investor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING], true);
    }
}
```

### File: `app/Modules/Trading/Models/Trade.php`

```php
<?php

namespace App\Modules\Trading\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    use HasFactory;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    public const SETTLEMENT_PENDING = 'pending';
    public const SETTLEMENT_SETTLED = 'settled';
    public const SETTLEMENT_FAILED  = 'failed';

    protected $fillable = [
        'offering_id',
        'buy_order_id',
        'sell_order_id',
        'buyer_id',
        'seller_id',
        'quantity',
        'price',
        'total_amount',
        'platform_fee',
        'vat_amount',
        'settlement_status',
        'settled_at',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'price'        => 'decimal:2',
        'total_amount' => 'integer',
        'platform_fee' => 'decimal:2',
        'vat_amount'   => 'decimal:2',
        'settled_at'   => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function offering(): BelongsTo
    {
        return $this->belongsTo(ShareOffering::class, 'offering_id');
    }

    public function buyOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'buy_order_id');
    }

    public function sellOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'sell_order_id');
    }
}
```

### File: `app/Modules/Trading/Models/DividendDeclaration.php`

```php
<?php

namespace App\Modules\Trading\Models;

use App\Modules\Directory\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DividendDeclaration extends Model
{
    use HasFactory;

    public const STATUS_DECLARED   = 'declared';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PAID       = 'paid';
    public const STATUS_CANCELLED  = 'cancelled';

    protected $fillable = [
        'company_id',
        'offering_id',
        'amount_per_share',
        'record_date',
        'payment_date',
        'status',
        'total_payout',
    ];

    protected $casts = [
        'amount_per_share' => 'decimal:4',
        'record_date'      => 'date',
        'payment_date'     => 'date',
        'total_payout'     => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function offering(): BelongsTo
    {
        return $this->belongsTo(ShareOffering::class, 'offering_id');
    }
}
```

### File: `app/Modules/Trading/Services/OfferingService.php`

Owns offering lifecycle: create, update, listing/search, document upload, and
the CMF approval state machine.

```php
<?php

namespace App\Modules\Trading\Services;

use App\Modules\Trading\Models\OfferingDocument;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OfferingService
{
    /**
     * Public, paginated listing. Uses Scout when a search term is supplied,
     * otherwise a filtered Eloquent query restricted to public statuses.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        if ($search) {
            $builder = ShareOffering::search($search);

            if (!empty($filters['status'])) {
                $builder->where('status', $filters['status']);
            }
            if (!empty($filters['company_id'])) {
                $builder->where('company_id', $filters['company_id']);
            }

            return $builder->paginate($perPage);
        }

        $query = ShareOffering::query()
            ->whereIn('status', ShareOffering::PUBLIC_STATUSES);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['instrument_type'])) {
            $query->where('instrument_type', $filters['instrument_type']);
        }
        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function create(array $data): ShareOffering
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = ShareOffering::STATUS_DRAFT;
            $data['amount_raised'] = 0;
            $data['shares_sold'] = 0;

            return ShareOffering::create($data);
        });
    }

    public function update(ShareOffering $offering, array $data): ShareOffering
    {
        // Once submitted to CMF, financial terms are locked.
        if ($offering->status !== ShareOffering::STATUS_DRAFT) {
            throw new RuntimeException('Only draft offerings can be edited.');
        }

        $offering->update($data);

        return $offering->fresh();
    }

    public function delete(ShareOffering $offering): void
    {
        $offering->delete();
    }

    public function addDocument(ShareOffering $offering, UploadedFile $file, array $meta): OfferingDocument
    {
        $path = $file->store("offerings/{$offering->id}/documents", 'local');

        return $offering->documents()->create([
            'type'       => $meta['type'],
            'title_fr'   => $meta['title_fr'],
            'title_en'   => $meta['title_en'] ?? null,
            'file_path'  => $path,
            'visibility' => $meta['visibility'] ?? 'investors_only',
        ]);
    }

    /**
     * Company submits a draft offering to the CMF for approval.
     */
    public function submitToCmf(ShareOffering $offering): ShareOffering
    {
        if ($offering->status !== ShareOffering::STATUS_DRAFT) {
            throw new RuntimeException('Only draft offerings can be submitted to the CMF.');
        }

        $offering->update(['status' => ShareOffering::STATUS_PENDING_CMF]);

        return $offering->fresh();
    }

    /**
     * CMF reviewer approves a pending offering. Records reviewer + timestamp and
     * logs a row in cmf_approvals.
     */
    public function cmfApprove(ShareOffering $offering, string $reviewerId, ?string $notes = null): ShareOffering
    {
        if ($offering->status !== ShareOffering::STATUS_PENDING_CMF) {
            throw new RuntimeException('Only offerings pending CMF review can be approved.');
        }

        return DB::transaction(function () use ($offering, $reviewerId, $notes) {
            $offering->update([
                'status'          => ShareOffering::STATUS_CMF_APPROVED,
                'cmf_reviewer_id' => $reviewerId,
                'cmf_approved_at' => now(),
                'cmf_notes'       => $notes,
            ]);

            DB::table('cmf_approvals')->insert([
                'offering_id' => $offering->id,
                'reviewer_id' => $reviewerId,
                'decision'    => 'approved',
                'notes_en'    => $notes,
                'decided_at'  => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return $offering->fresh();
        });
    }

    public function cmfReject(ShareOffering $offering, string $reviewerId, string $reason): ShareOffering
    {
        if ($offering->status !== ShareOffering::STATUS_PENDING_CMF) {
            throw new RuntimeException('Only offerings pending CMF review can be rejected.');
        }

        return DB::transaction(function () use ($offering, $reviewerId, $reason) {
            $offering->update([
                'status'          => ShareOffering::STATUS_DRAFT,
                'cmf_reviewer_id' => $reviewerId,
                'cmf_notes'       => $reason,
            ]);

            DB::table('cmf_approvals')->insert([
                'offering_id' => $offering->id,
                'reviewer_id' => $reviewerId,
                'decision'    => 'rejected',
                'notes_en'    => $reason,
                'decided_at'  => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return $offering->fresh();
        });
    }
}
```

### File: `app/Modules/Trading/Services/TradingService.php`

Owns the order book: placing orders, listing the open book, cancelling.

```php
<?php

namespace App\Modules\Trading\Services;

use App\Modules\Trading\Models\Order;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TradingService
{
    /** Open orders sitting on the book for an offering. */
    public function openOrders(ShareOffering $offering, ?string $type = null): Collection
    {
        return $offering->orders()
            ->whereIn('status', Order::OPEN_STATUSES)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByDesc('unit_price')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Place a buy/sell order against an open offering.
     * total_amount is computed server-side from quantity * unit_price.
     */
    public function placeOrder(ShareOffering $offering, string $investorId, array $data): Order
    {
        if (!$offering->isOpenForInvestment()) {
            throw new RuntimeException('Offering is not open for investment.');
        }

        $quantity  = (int) $data['quantity'];
        $unitPrice = (float) ($data['unit_price'] ?? $offering->share_price);
        $total     = (int) round($quantity * $unitPrice);

        if ($total < $offering->min_investment) {
            throw new RuntimeException('Order is below the minimum investment for this offering.');
        }

        return DB::transaction(function () use ($offering, $investorId, $data, $quantity, $unitPrice, $total) {
            return $offering->orders()->create([
                'investor_id'    => $investorId,
                'type'           => $data['type'] ?? Order::TYPE_BUY,
                'status'         => Order::STATUS_PENDING,
                'quantity'       => $quantity,
                'unit_price'     => $unitPrice,
                'total_amount'   => $total,
                'payment_method' => $data['payment_method'] ?? null,
                'expires_at'     => now()->addDay(),
            ]);
        });
    }

    /**
     * Cancel an order the investor owns. Only open orders can be cancelled.
     */
    public function cancelOrder(Order $order, string $investorId): Order
    {
        if ($order->investor_id !== $investorId) {
            throw new RuntimeException('You may only cancel your own orders.');
        }

        if (!$order->isCancellable()) {
            throw new RuntimeException('This order can no longer be cancelled.');
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        return $order->fresh();
    }
}
```

### File: `database/factories/ShareOfferingFactory.php`

```php
<?php

namespace Database\Factories;

use App\Modules\Directory\Models\Company;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShareOfferingFactory extends Factory
{
    protected $model = ShareOffering::class;

    public function definition(): array
    {
        $sharePrice  = $this->faker->numberBetween(1000, 50000);
        $totalShares = $this->faker->numberBetween(1000, 100000);

        return [
            'company_id'       => Company::factory(),
            'title_fr'         => 'Offre ' . $this->faker->unique()->company(),
            'title_en'         => 'Offering ' . $this->faker->company(),
            'summary_fr'       => $this->faker->paragraph(),
            'summary_en'       => $this->faker->paragraph(),
            'instrument_type'  => 'ordinary_shares',
            'status'           => ShareOffering::STATUS_DRAFT,
            'target_amount'    => $sharePrice * $totalShares,
            'minimum_amount'   => $sharePrice * (int) ($totalShares / 2),
            'maximum_amount'   => null,
            'amount_raised'    => 0,
            'share_price'      => $sharePrice,
            'total_shares'     => $totalShares,
            'shares_sold'      => 0,
            'equity_offered'   => $this->faker->randomFloat(2, 1, 40),
            'min_investment'   => 10000,
            'max_investment'   => null,
            'open_date'        => now()->toDateString(),
            'close_date'       => now()->addMonths(3)->toDateString(),
            'currency'         => 'XAF',
            'platform_fee_pct' => 2.50,
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => ['status' => ShareOffering::STATUS_OPEN]);
    }

    public function pendingCmf(): static
    {
        return $this->state(fn () => ['status' => ShareOffering::STATUS_PENDING_CMF]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status'          => ShareOffering::STATUS_CMF_APPROVED,
            'cmf_approved_at' => now(),
        ]);
    }
}
```

### Provider: `app/Modules/Trading/Providers/TradingServiceProvider.php`

```php
<?php

namespace App\Modules\Trading\Providers;

use App\Modules\Trading\Services\OfferingService;
use App\Modules\Trading\Services\TradingService;
use Illuminate\Support\ServiceProvider;

class TradingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OfferingService::class);
        $this->app->singleton(TradingService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
```

> Register `TradingServiceProvider` in `bootstrap/providers.php` (alongside the
> Directory provider). Create an empty `app/Modules/Trading/Routes/api.php` with
> `<?php use Illuminate\Support\Facades\Route;` for now — it is filled in D3-2.

### Verification & commit

```bash
php artisan test --filter=Trading   # nothing yet — just ensure boot is clean
php artisan tinker --execute="App\Modules\Trading\Models\ShareOffering::factory()->make();"
git add app/Modules/Trading database/factories/ShareOfferingFactory.php bootstrap/providers.php
git commit -m "feat(trading): share offering + order + trade + dividend models, services, factory"
```

---

# Task D3-2: Share Offering CRUD Endpoints (TDD)

Endpoints (module routes loaded by `TradingServiceProvider`):

| Method | URI | Auth | Notes |
|--------|-----|------|-------|
| GET | `/api/v1/offerings` | public | list + search/filter |
| POST | `/api/v1/offerings` | `companies:write` | create draft |
| GET | `/api/v1/offerings/{id}` | public | show |
| PUT | `/api/v1/offerings/{id}` | `companies:write` (owner) | update draft only |
| DELETE | `/api/v1/offerings/{id}` | `companies:write` (owner) | soft delete |
| POST | `/api/v1/offerings/{id}/documents` | `companies:write` (owner) | upload |
| POST | `/api/v1/offerings/{id}/submit-cmf` | `companies:write` (owner) | → `pending_cmf` |
| POST | `/api/v1/admin/offerings/{id}/cmf-approve` | `role:cmf_reviewer` | → `cmf_approved` |
| POST | `/api/v1/admin/offerings/{id}/cmf-reject` | `role:cmf_reviewer` | → `draft` |

### STEP 1 — RED: write the failing test

### File: `tests/Feature/Trading/OfferingTest.php`

```php
<?php

namespace Tests\Feature\Trading;

use App\Modules\Auth\Models\User;
use App\Modules\Directory\Models\Company;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OfferingTest extends TestCase
{
    use RefreshDatabase;

    private function owner(Company $company): User
    {
        $user = User::factory()->create();
        $user->assignRole('company_owner');
        $company->members()->create([
            'user_id' => $user->id, 'role' => 'owner', 'is_active' => true, 'joined_at' => now(),
        ]);

        return $user;
    }

    private function cmfReviewer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('cmf_reviewer');

        return $user;
    }

    private function payload(Company $company, array $overrides = []): array
    {
        return array_merge([
            'company_id'      => $company->id,
            'title_fr'        => 'Levee de fonds Serie A',
            'title_en'        => 'Series A raise',
            'summary_fr'      => 'Resume.',
            'instrument_type' => 'ordinary_shares',
            'target_amount'   => 50000000,
            'share_price'     => 5000,
            'total_shares'    => 10000,
            'equity_offered'  => 20.0,
            'min_investment'  => 10000,
        ], $overrides);
    }

    public function test_public_can_list_open_offerings(): void
    {
        ShareOffering::factory()->open()->count(2)->create();
        ShareOffering::factory()->create(['status' => 'draft']); // hidden

        $this->getJson('/api/v1/offerings')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'title_fr', 'status', 'share_price']], 'meta']);
    }

    public function test_owner_can_create_an_offering(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);

        $this->postJson('/api/v1/offerings', $this->payload($company))
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.title_fr', 'Levee de fonds Serie A');

        $this->assertDatabaseHas('share_offerings', [
            'title_fr' => 'Levee de fonds Serie A', 'status' => 'draft',
        ]);
    }

    public function test_creating_offering_requires_companies_write_scope(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), []); // no scope

        $this->postJson('/api/v1/offerings', $this->payload($company))
            ->assertForbidden();
    }

    public function test_create_validation_fails_without_required_fields(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);

        $this->postJson('/api/v1/offerings', ['company_id' => $company->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title_fr', 'target_amount', 'share_price', 'total_shares']);
    }

    public function test_public_can_view_a_single_offering(): void
    {
        $offering = ShareOffering::factory()->open()->create();

        $this->getJson("/api/v1/offerings/{$offering->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $offering->id);
    }

    public function test_owner_can_update_a_draft_offering(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);
        $offering = ShareOffering::factory()->create(['company_id' => $company->id]);

        $this->putJson("/api/v1/offerings/{$offering->id}", ['title_fr' => 'Modifie'])
            ->assertOk()
            ->assertJsonPath('data.title_fr', 'Modifie');
    }

    public function test_non_owner_cannot_update_an_offering(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);
        $other = ShareOffering::factory()->create(); // different company

        $this->putJson("/api/v1/offerings/{$other->id}", ['title_fr' => 'Hijack'])
            ->assertForbidden();
    }

    public function test_owner_can_soft_delete_an_offering(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);
        $offering = ShareOffering::factory()->create(['company_id' => $company->id]);

        $this->deleteJson("/api/v1/offerings/{$offering->id}")->assertNoContent();
        $this->assertSoftDeleted('share_offerings', ['id' => $offering->id]);
    }

    public function test_owner_can_upload_an_offering_document(): void
    {
        Storage::fake('local');
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);
        $offering = ShareOffering::factory()->create(['company_id' => $company->id]);

        $this->postJson("/api/v1/offerings/{$offering->id}/documents", [
            'type'     => 'prospectus',
            'title_fr' => 'Prospectus',
            'file'     => UploadedFile::fake()->create('prospectus.pdf', 200, 'application/pdf'),
        ])->assertCreated();

        $this->assertDatabaseHas('offering_documents', [
            'offering_id' => $offering->id, 'type' => 'prospectus',
        ]);
    }

    public function test_owner_can_submit_offering_to_cmf(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);
        $offering = ShareOffering::factory()->create(['company_id' => $company->id]);

        $this->postJson("/api/v1/offerings/{$offering->id}/submit-cmf")
            ->assertOk()
            ->assertJsonPath('data.status', 'pending_cmf');
    }

    public function test_cmf_reviewer_can_approve_a_pending_offering(): void
    {
        Passport::actingAs($this->cmfReviewer(), []);
        $offering = ShareOffering::factory()->pendingCmf()->create();

        $this->postJson("/api/v1/admin/offerings/{$offering->id}/cmf-approve", [
            'notes' => 'Looks good.',
        ])->assertOk()->assertJsonPath('data.status', 'cmf_approved');

        $this->assertDatabaseHas('cmf_approvals', [
            'offering_id' => $offering->id, 'decision' => 'approved',
        ]);
    }

    public function test_non_reviewer_cannot_approve_offering(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);
        $offering = ShareOffering::factory()->pendingCmf()->create();

        $this->postJson("/api/v1/admin/offerings/{$offering->id}/cmf-approve")
            ->assertForbidden();
    }

    public function test_cmf_reviewer_can_reject_a_pending_offering(): void
    {
        Passport::actingAs($this->cmfReviewer(), []);
        $offering = ShareOffering::factory()->pendingCmf()->create();

        $this->postJson("/api/v1/admin/offerings/{$offering->id}/cmf-reject", [
            'reason' => 'Incomplete prospectus.',
        ])->assertOk()->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('cmf_approvals', [
            'offering_id' => $offering->id, 'decision' => 'rejected',
        ]);
    }
}
```

Run it — every test fails (no routes/controller). **RED confirmed.**

```bash
php artisan test --filter=OfferingTest
```

### STEP 2 — GREEN: implement

### File: `app/Modules/Trading/Requests/CreateOfferingRequest.php`

```php
<?php

namespace App\Modules\Trading\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOfferingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'company_id'      => ['required', 'uuid', 'exists:companies,id'],
            'title_fr'        => ['required', 'string', 'max:255'],
            'title_en'        => ['nullable', 'string', 'max:255'],
            'summary_fr'      => ['nullable', 'string'],
            'summary_en'      => ['nullable', 'string'],
            'instrument_type' => ['required', Rule::in(['ordinary_shares', 'preference_shares', 'bonds', 'convertible_notes'])],
            'target_amount'   => ['required', 'integer', 'min:1'],
            'minimum_amount'  => ['nullable', 'integer', 'min:0'],
            'maximum_amount'  => ['nullable', 'integer', 'min:0'],
            'share_price'     => ['required', 'numeric', 'min:0.01'],
            'total_shares'    => ['required', 'integer', 'min:1'],
            'equity_offered'  => ['nullable', 'numeric', 'between:0,100'],
            'min_investment'  => ['nullable', 'integer', 'min:0'],
            'max_investment'  => ['nullable', 'integer', 'min:0'],
            'open_date'       => ['nullable', 'date'],
            'close_date'      => ['nullable', 'date', 'after_or_equal:open_date'],
            'currency'        => ['nullable', 'string', 'size:3'],
        ];
    }
}
```

### File: `app/Modules/Trading/Requests/UpdateOfferingRequest.php`

```php
<?php

namespace App\Modules\Trading\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfferingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title_fr'        => ['sometimes', 'string', 'max:255'],
            'title_en'        => ['nullable', 'string', 'max:255'],
            'summary_fr'      => ['nullable', 'string'],
            'summary_en'      => ['nullable', 'string'],
            'instrument_type' => ['sometimes', Rule::in(['ordinary_shares', 'preference_shares', 'bonds', 'convertible_notes'])],
            'target_amount'   => ['sometimes', 'integer', 'min:1'],
            'minimum_amount'  => ['nullable', 'integer', 'min:0'],
            'maximum_amount'  => ['nullable', 'integer', 'min:0'],
            'share_price'     => ['sometimes', 'numeric', 'min:0.01'],
            'total_shares'    => ['sometimes', 'integer', 'min:1'],
            'equity_offered'  => ['nullable', 'numeric', 'between:0,100'],
            'min_investment'  => ['nullable', 'integer', 'min:0'],
            'open_date'       => ['nullable', 'date'],
            'close_date'      => ['nullable', 'date', 'after_or_equal:open_date'],
        ];
    }
}
```

### File: `app/Modules/Trading/Requests/UploadOfferingDocumentRequest.php`

```php
<?php

namespace App\Modules\Trading\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadOfferingDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type'       => ['required', Rule::in(['prospectus', 'financial_statement', 'business_plan', 'investor_presentation', 'term_sheet', 'other'])],
            'title_fr'   => ['required', 'string', 'max:255'],
            'title_en'   => ['nullable', 'string', 'max:255'],
            'visibility' => ['nullable', Rule::in(['public', 'investors_only', 'cmf_only'])],
            'file'       => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
        ];
    }
}
```

### File: `app/Modules/Trading/Resources/OfferingResource.php`

```php
<?php

namespace App\Modules\Trading\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id'              => $this->id,
            'company_id'      => $this->company_id,
            'title_fr'        => $this->title_fr,
            'title_en'        => $this->title_en,
            'title'           => $locale === 'en' ? ($this->title_en ?: $this->title_fr) : $this->title_fr,
            'summary_fr'      => $this->summary_fr,
            'summary_en'      => $this->summary_en,
            'instrument_type' => $this->instrument_type,
            'status'          => $this->status,
            'target_amount'   => (int) $this->target_amount,
            'minimum_amount'  => $this->minimum_amount !== null ? (int) $this->minimum_amount : null,
            'maximum_amount'  => $this->maximum_amount !== null ? (int) $this->maximum_amount : null,
            'amount_raised'   => (int) $this->amount_raised,
            'share_price'     => (float) $this->share_price,
            'total_shares'    => (int) $this->total_shares,
            'shares_sold'     => (int) $this->shares_sold,
            'equity_offered'  => $this->equity_offered !== null ? (float) $this->equity_offered : null,
            'min_investment'  => (int) $this->min_investment,
            'max_investment'  => $this->max_investment !== null ? (int) $this->max_investment : null,
            'open_date'       => optional($this->open_date)->toDateString(),
            'close_date'      => optional($this->close_date)->toDateString(),
            'currency'        => $this->currency,
            'platform_fee_pct'=> (float) $this->platform_fee_pct,
            'cmf_approved_at' => optional($this->cmf_approved_at)->toIso8601String(),
            'documents'       => OfferingDocumentResource::collection($this->whenLoaded('documents')),
            'created_at'      => optional($this->created_at)->toIso8601String(),
            'updated_at'      => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
```

### File: `app/Modules/Trading/Resources/OfferingDocumentResource.php`

```php
<?php

namespace App\Modules\Trading\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferingDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->type,
            'title_fr'   => $this->title_fr,
            'title_en'   => $this->title_en,
            'visibility' => $this->visibility,
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
```

### File: `app/Modules/Trading/Resources/OfferingCollection.php`

```php
<?php

namespace App\Modules\Trading\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OfferingCollection extends ResourceCollection
{
    public $collects = OfferingResource::class;
}
```

### File: `app/Modules/Trading/Controllers/OfferingController.php`

```php
<?php

namespace App\Modules\Trading\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Trading\Models\ShareOffering;
use App\Modules\Trading\Requests\CreateOfferingRequest;
use App\Modules\Trading\Requests\UpdateOfferingRequest;
use App\Modules\Trading\Requests\UploadOfferingDocumentRequest;
use App\Modules\Trading\Resources\OfferingCollection;
use App\Modules\Trading\Resources\OfferingDocumentResource;
use App\Modules\Trading\Resources\OfferingResource;
use App\Modules\Trading\Services\OfferingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferingController extends Controller
{
    public function __construct(
        private readonly OfferingService $offerings
    ) {
    }

    public function index(Request $request): OfferingCollection
    {
        $paginator = $this->offerings->list(
            $request->only(['search', 'status', 'instrument_type', 'company_id']),
            (int) $request->integer('per_page', 15)
        );

        return new OfferingCollection($paginator);
    }

    public function store(CreateOfferingRequest $request): JsonResponse
    {
        $company = \App\Modules\Directory\Models\Company::findOrFail($request->validated()['company_id']);
        $this->authorizeCompanyOwner($company, $request->user()->id);

        $offering = $this->offerings->create($request->validated());

        return (new OfferingResource($offering))->response()->setStatusCode(201);
    }

    public function show(string $id): OfferingResource
    {
        $offering = ShareOffering::with('documents')->findOrFail($id);

        return new OfferingResource($offering);
    }

    public function update(UpdateOfferingRequest $request, string $id): OfferingResource
    {
        $offering = ShareOffering::findOrFail($id);
        $this->authorizeOfferingOwner($offering, $request->user()->id);

        return new OfferingResource($this->offerings->update($offering, $request->validated()));
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $offering = ShareOffering::findOrFail($id);
        $this->authorizeOfferingOwner($offering, $request->user()->id);

        $this->offerings->delete($offering);

        return response()->json(null, 204);
    }

    public function uploadDocument(UploadOfferingDocumentRequest $request, string $id): JsonResponse
    {
        $offering = ShareOffering::findOrFail($id);
        $this->authorizeOfferingOwner($offering, $request->user()->id);

        $document = $this->offerings->addDocument(
            $offering,
            $request->file('file'),
            $request->validated()
        );

        return (new OfferingDocumentResource($document))->response()->setStatusCode(201);
    }

    public function submitToCmf(Request $request, string $id): OfferingResource
    {
        $offering = ShareOffering::findOrFail($id);
        $this->authorizeOfferingOwner($offering, $request->user()->id);

        return new OfferingResource($this->offerings->submitToCmf($offering));
    }

    public function cmfApprove(Request $request, string $id): OfferingResource
    {
        $offering = ShareOffering::findOrFail($id);

        return new OfferingResource(
            $this->offerings->cmfApprove($offering, $request->user()->id, $request->input('notes'))
        );
    }

    public function cmfReject(Request $request, string $id): OfferingResource
    {
        $offering = ShareOffering::findOrFail($id);
        $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        return new OfferingResource(
            $this->offerings->cmfReject($offering, $request->user()->id, $request->input('reason'))
        );
    }

    private function authorizeOfferingOwner(ShareOffering $offering, string $userId): void
    {
        $this->authorizeCompanyOwner($offering->company, $userId);
    }

    private function authorizeCompanyOwner(?\App\Modules\Directory\Models\Company $company, string $userId): void
    {
        abort_if($company === null, 404);

        $isOwner = $company->members()
            ->where('user_id', $userId)
            ->where('role', 'owner')
            ->exists();

        abort_unless($isOwner, 403, 'Only the company owner may manage this offering.');
    }
}
```

### File: `app/Modules/Trading/Routes/api.php`

```php
<?php

use App\Modules\Trading\Controllers\OfferingController;
use App\Modules\Trading\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('api')->group(function () {
    // Public reads
    Route::get('offerings', [OfferingController::class, 'index']);
    Route::get('offerings/{id}', [OfferingController::class, 'show']);
    Route::get('offerings/{id}/orders', [OrderController::class, 'index']);

    Route::middleware(['auth:api'])->group(function () {
        // Company-side writes
        Route::middleware('scopes:companies:write')->group(function () {
            Route::post('offerings', [OfferingController::class, 'store']);
            Route::put('offerings/{id}', [OfferingController::class, 'update']);
            Route::delete('offerings/{id}', [OfferingController::class, 'destroy']);
            Route::post('offerings/{id}/documents', [OfferingController::class, 'uploadDocument']);
            Route::post('offerings/{id}/submit-cmf', [OfferingController::class, 'submitToCmf']);
        });

        // CMF reviewer actions
        Route::middleware('role:cmf_reviewer|super_admin')->group(function () {
            Route::post('admin/offerings/{id}/cmf-approve', [OfferingController::class, 'cmfApprove']);
            Route::post('admin/offerings/{id}/cmf-reject', [OfferingController::class, 'cmfReject']);
        });

        // Order book writes (D3-3)
        Route::middleware('scopes:investor:pledge')->group(function () {
            Route::post('offerings/{id}/orders', [OrderController::class, 'store']);
            Route::delete('offerings/{id}/orders/{orderId}', [OrderController::class, 'destroy']);
        });
    });
});
```

> **Service-thrown `RuntimeException` → HTTP:** the services throw
> `RuntimeException` for illegal state transitions. Add a handler mapping in
> `bootstrap/app.php` so these become `422`:
> ```php
> ->withExceptions(function (Illuminate\Foundation\Configuration\Exceptions $e) {
>     $e->render(function (\RuntimeException $ex, $request) {
>         if ($request->is('api/*')) {
>             return response()->json(['message' => $ex->getMessage()], 422);
>         }
>     });
> });
> ```

Run until green:

```bash
php artisan test --filter=OfferingTest
```

### Commit

```bash
git add app/Modules/Trading tests/Feature/Trading/OfferingTest.php bootstrap/app.php
git commit -m "feat(trading): offering CRUD + document upload + CMF approval workflow (TDD)"
```

---

# Task D3-3: Order Book Endpoints

| Method | URI | Auth |
|--------|-----|------|
| GET | `/api/v1/offerings/{id}/orders` | public (open book) |
| POST | `/api/v1/offerings/{id}/orders` | `investor:pledge` |
| DELETE | `/api/v1/offerings/{id}/orders/{orderId}` | `investor:pledge` (owner) |

Routes are already declared in the Trading `api.php` above (`OrderController`).

### STEP 1 — RED

### File: `tests/Feature/Trading/OrderBookTest.php`

```php
<?php

namespace Tests\Feature\Trading;

use App\Modules\Auth\Models\User;
use App\Modules\Trading\Models\Order;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OrderBookTest extends TestCase
{
    use RefreshDatabase;

    private function investor(): User
    {
        $user = User::factory()->create();
        $user->assignRole('investor');

        return $user;
    }

    public function test_public_can_view_the_open_order_book(): void
    {
        $offering = ShareOffering::factory()->open()->create();
        Order::factory()->count(2)->create([
            'offering_id' => $offering->id, 'status' => 'pending',
        ]);

        $this->getJson("/api/v1/offerings/{$offering->id}/orders")
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'type', 'quantity', 'unit_price', 'status']]]);
    }

    public function test_investor_can_place_a_buy_order(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create(['share_price' => 5000, 'min_investment' => 10000]);

        $this->postJson("/api/v1/offerings/{$offering->id}/orders", [
            'type' => 'buy', 'quantity' => 10,
        ])->assertCreated()
          ->assertJsonPath('data.type', 'buy')
          ->assertJsonPath('data.quantity', 10)
          ->assertJsonPath('data.total_amount', 50000);

        $this->assertDatabaseHas('orders', [
            'offering_id' => $offering->id, 'investor_id' => $investor->id, 'status' => 'pending',
        ]);
    }

    public function test_placing_order_requires_investor_pledge_scope(): void
    {
        Passport::actingAs($this->investor(), []); // no scope
        $offering = ShareOffering::factory()->open()->create();

        $this->postJson("/api/v1/offerings/{$offering->id}/orders", ['quantity' => 5])
            ->assertForbidden();
    }

    public function test_cannot_place_order_on_a_closed_offering(): void
    {
        Passport::actingAs($this->investor(), ['investor:pledge']);
        $offering = ShareOffering::factory()->create(['status' => 'draft']);

        $this->postJson("/api/v1/offerings/{$offering->id}/orders", ['quantity' => 10])
            ->assertStatus(422);
    }

    public function test_investor_can_cancel_their_own_order(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create();
        $order = Order::factory()->create([
            'offering_id' => $offering->id, 'investor_id' => $investor->id, 'status' => 'pending',
        ]);

        $this->deleteJson("/api/v1/offerings/{$offering->id}/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_investor_cannot_cancel_someone_elses_order(): void
    {
        Passport::actingAs($this->investor(), ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create();
        $order = Order::factory()->create([
            'offering_id' => $offering->id, 'status' => 'pending', // owned by someone else
        ]);

        $this->deleteJson("/api/v1/offerings/{$offering->id}/orders/{$order->id}")
            ->assertStatus(422);
    }
}
```

> Add an `OrderFactory` (needed by the tests above):

### File: `database/factories/OrderFactory.php`

```php
<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Trading\Models\Order;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 100);
        $price    = $this->faker->numberBetween(1000, 50000);

        return [
            'offering_id'  => ShareOffering::factory(),
            'investor_id'  => User::factory(),
            'type'         => 'buy',
            'status'       => 'pending',
            'quantity'     => $quantity,
            'unit_price'   => $price,
            'total_amount' => $quantity * $price,
        ];
    }
}
```

Wire the factory on the model (already done in D3-1: add
`protected static function newFactory()` returning `OrderFactory::new()` to
`Order`, or rely on Laravel's auto-resolution since `Order` lives outside
`App\Models`). **Add explicit factory resolution** to `Order`:

```php
protected static function newFactory(): \Database\Factories\OrderFactory
{
    return \Database\Factories\OrderFactory::new();
}
```

### STEP 2 — GREEN

### File: `app/Modules/Trading/Requests/PlaceOrderRequest.php`

```php
<?php

namespace App\Modules\Trading\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type'           => ['nullable', Rule::in(['buy', 'sell'])],
            'quantity'       => ['required', 'integer', 'min:1'],
            'unit_price'     => ['nullable', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', Rule::in(['mtn_momo', 'orange_money', 'bank_transfer'])],
        ];
    }
}
```

### File: `app/Modules/Trading/Resources/OrderResource.php`

```php
<?php

namespace App\Modules\Trading\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'offering_id'     => $this->offering_id,
            'investor_id'     => $this->investor_id,
            'type'            => $this->type,
            'status'          => $this->status,
            'quantity'        => (int) $this->quantity,
            'unit_price'      => (float) $this->unit_price,
            'total_amount'    => (int) $this->total_amount,
            'filled_quantity' => (int) $this->filled_quantity,
            'payment_method'  => $this->payment_method,
            'expires_at'      => optional($this->expires_at)->toIso8601String(),
            'created_at'      => optional($this->created_at)->toIso8601String(),
        ];
    }
}
```

### File: `app/Modules/Trading/Controllers/OrderController.php`

```php
<?php

namespace App\Modules\Trading\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Trading\Models\Order;
use App\Modules\Trading\Models\ShareOffering;
use App\Modules\Trading\Requests\PlaceOrderRequest;
use App\Modules\Trading\Resources\OrderResource;
use App\Modules\Trading\Services\TradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly TradingService $trading
    ) {
    }

    public function index(Request $request, string $id): JsonResponse
    {
        $offering = ShareOffering::findOrFail($id);
        $orders = $this->trading->openOrders($offering, $request->query('type'));

        return OrderResource::collection($orders)->response();
    }

    public function store(PlaceOrderRequest $request, string $id): JsonResponse
    {
        $offering = ShareOffering::findOrFail($id);

        $order = $this->trading->placeOrder(
            $offering,
            $request->user()->id,
            $request->validated()
        );

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, string $id, string $orderId): JsonResponse
    {
        $order = Order::where('offering_id', $id)->findOrFail($orderId);

        $order = $this->trading->cancelOrder($order, $request->user()->id);

        return (new OrderResource($order))->response();
    }
}
```

Run until green:

```bash
php artisan test --filter=OrderBookTest
```

### Commit

```bash
git add app/Modules/Trading tests/Feature/Trading/OrderBookTest.php database/factories/OrderFactory.php
git commit -m "feat(trading): order book — place/list/cancel orders (TDD)"
```

---

# Task D3-4: Investors Module Models

### File: `app/Modules/Investors/Models/InvestorProfile.php`

```php
<?php

namespace App\Modules\Investors\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'investor_type',
        'accreditation_level',
        'national_id',
        'id_type',
        'dob',
        'nationality',
        'occupation',
        'employer',
        'annual_income',
        'net_worth',
        'risk_tolerance',
        'is_pep',
        'is_sanctioned',
        'bank_name',
        'bank_account',
        'bank_rib',
    ];

    protected $casts = [
        'dob'           => 'date',
        'annual_income' => 'integer',
        'net_worth'     => 'integer',
        'is_pep'        => 'boolean',
        'is_sanctioned' => 'boolean',
    ];

    protected $hidden = ['national_id', 'bank_account', 'bank_rib'];

    protected static function newFactory(): \Database\Factories\InvestorProfileFactory
    {
        return \Database\Factories\InvestorProfileFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

### File: `app/Modules/Investors/Models/KycApplication.php`

```php
<?php

namespace App\Modules\Investors\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KycApplication extends Model
{
    use HasFactory;

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_EXPIRED   = 'expired';

    protected $fillable = [
        'user_id',
        'tier',
        'status',
        'reviewed_by',
        'rejection_reason_fr',
        'rejection_reason_en',
        'submitted_at',
        'reviewed_at',
        'expires_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(KycDocument::class, 'kyc_application_id');
    }
}
```

### File: `app/Modules/Investors/Models/KycDocument.php`

```php
<?php

namespace App\Modules\Investors\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'kyc_application_id',
        'type',
        'file_path',
        'original_name',
        'is_accepted',
        'rejection_reason',
    ];

    protected $casts = [
        'is_accepted' => 'boolean',
    ];

    protected $hidden = ['file_path'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(KycApplication::class, 'kyc_application_id');
    }
}
```

### File: `app/Modules/Investors/Models/InvestmentPledge.php`

```php
<?php

namespace App\Modules\Investors\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentPledge extends Model
{
    use HasFactory;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    public const STATUS_PENDING           = 'pending';
    public const STATUS_PAYMENT_INITIATED = 'payment_initiated';
    public const STATUS_PAYMENT_RECEIVED  = 'payment_received';
    public const STATUS_ORDER_CREATED     = 'order_created';
    public const STATUS_CANCELLED         = 'cancelled';
    public const STATUS_EXPIRED           = 'expired';

    public const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAYMENT_INITIATED,
        self::STATUS_PAYMENT_RECEIVED,
        self::STATUS_ORDER_CREATED,
    ];

    protected $fillable = [
        'investor_id',
        'offering_id',
        'amount',
        'shares_requested',
        'status',
        'payment_method',
        'payment_reference',
        'payment_initiated_at',
        'payment_confirmed_at',
        'expires_at',
    ];

    protected $casts = [
        'amount'               => 'integer',
        'shares_requested'     => 'integer',
        'payment_initiated_at' => 'datetime',
        'payment_confirmed_at' => 'datetime',
        'expires_at'           => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    protected static function newFactory(): \Database\Factories\InvestmentPledgeFactory
    {
        return \Database\Factories\InvestmentPledgeFactory::new();
    }

    public function investor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function offering(): BelongsTo
    {
        return $this->belongsTo(ShareOffering::class, 'offering_id');
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PAYMENT_INITIATED], true);
    }
}
```

### File: `app/Modules/Investors/Services/InvestorService.php`

```php
<?php

namespace App\Modules\Investors\Services;

use App\Modules\Investors\Models\InvestmentPledge;
use App\Modules\Investors\Models\InvestorProfile;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvestorService
{
    /** Get or create the investor's profile (1:1 with the user). */
    public function profileFor(string $userId): InvestorProfile
    {
        return InvestorProfile::firstOrCreate(['user_id' => $userId]);
    }

    public function updateProfile(string $userId, array $data): InvestorProfile
    {
        $profile = $this->profileFor($userId);
        $profile->update($data);

        return $profile->fresh();
    }

    /** Create a pledge against an open offering. */
    public function pledge(ShareOffering $offering, string $investorId, array $data): InvestmentPledge
    {
        if (!$offering->isOpenForInvestment()) {
            throw new RuntimeException('Offering is not open for pledges.');
        }

        $amount = (int) $data['amount'];

        if ($amount < $offering->min_investment) {
            throw new RuntimeException('Pledge is below the minimum investment for this offering.');
        }

        return DB::transaction(function () use ($offering, $investorId, $data, $amount) {
            $shares = isset($data['shares_requested'])
                ? (int) $data['shares_requested']
                : (int) floor($amount / (float) $offering->share_price);

            return InvestmentPledge::create([
                'investor_id'      => $investorId,
                'offering_id'      => $offering->id,
                'amount'           => $amount,
                'shares_requested' => $shares,
                'status'           => InvestmentPledge::STATUS_PENDING,
                'payment_method'   => $data['payment_method'] ?? null,
                'expires_at'       => now()->addDays(3),
            ]);
        });
    }

    /** Pledges for a single offering (company-owner / admin view). */
    public function pledgesForOffering(ShareOffering $offering): Collection
    {
        return $offering->pledges()->orderByDesc('created_at')->get();
    }

    /** The investor's own pledges across all offerings. */
    public function pledgesForInvestor(string $investorId): Collection
    {
        return InvestmentPledge::where('investor_id', $investorId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function cancelPledge(InvestmentPledge $pledge, string $investorId): InvestmentPledge
    {
        if ($pledge->investor_id !== $investorId) {
            throw new RuntimeException('You may only cancel your own pledges.');
        }

        if (!$pledge->isCancellable()) {
            throw new RuntimeException('This pledge can no longer be cancelled.');
        }

        $pledge->update(['status' => InvestmentPledge::STATUS_CANCELLED]);

        return $pledge->fresh();
    }

    /**
     * Build a portfolio summary from pledges + the portfolios snapshot row.
     */
    public function portfolioSummary(string $investorId): array
    {
        $pledges = InvestmentPledge::where('investor_id', $investorId)->get();

        $activePledges = $pledges->whereIn('status', InvestmentPledge::ACTIVE_STATUSES);
        $totalPledged  = (int) $activePledges->sum('amount');
        $companies     = ShareOffering::whereIn('id', $activePledges->pluck('offering_id'))
            ->distinct('company_id')
            ->count('company_id');

        $snapshot = DB::table('portfolios')->where('investor_id', $investorId)->first();

        return [
            'total_invested'           => (int) ($snapshot->total_invested ?? 0),
            'total_pledged'            => $totalPledged,
            'current_value'            => (int) ($snapshot->current_value ?? 0),
            'total_dividends_received' => (int) ($snapshot->total_dividends_received ?? 0),
            'companies_count'          => $companies,
            'active_pledges'           => $activePledges->count(),
            'pledges'                  => $pledges,
        ];
    }
}
```

### File: `app/Modules/Investors/Services/KycService.php`

```php
<?php

namespace App\Modules\Investors\Services;

use App\Modules\Investors\Models\KycApplication;
use App\Modules\Investors\Models\KycDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class KycService
{
    /** The user's current (latest) KYC application, if any. */
    public function currentApplication(string $userId): ?KycApplication
    {
        return KycApplication::where('user_id', $userId)
            ->latest()
            ->first();
    }

    /**
     * Submit a KYC application. Reuses a draft if present, otherwise creates one,
     * then moves it to "submitted".
     */
    public function submit(string $userId, array $data): KycApplication
    {
        return DB::transaction(function () use ($userId, $data) {
            $existing = KycApplication::where('user_id', $userId)
                ->whereIn('status', [KycApplication::STATUS_SUBMITTED, KycApplication::STATUS_IN_REVIEW])
                ->first();

            if ($existing) {
                throw new RuntimeException('A KYC application is already under review.');
            }

            $application = KycApplication::create([
                'user_id'      => $userId,
                'tier'         => $data['tier'] ?? 'basic',
                'status'       => KycApplication::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]);

            return $application;
        });
    }

    public function addDocument(KycApplication $application, UploadedFile $file, string $type): KycDocument
    {
        $path = $file->store("kyc/{$application->id}/documents", 'local');

        return $application->documents()->create([
            'type'          => $type,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);
    }

    /** Compliance: list applications awaiting review. */
    public function pending(int $perPage = 15): LengthAwarePaginator
    {
        return KycApplication::whereIn('status', [
            KycApplication::STATUS_SUBMITTED,
            KycApplication::STATUS_IN_REVIEW,
        ])->orderBy('submitted_at')->paginate($perPage);
    }

    public function approve(KycApplication $application, string $reviewerId): KycApplication
    {
        if ($application->status === KycApplication::STATUS_APPROVED) {
            throw new RuntimeException('Application is already approved.');
        }

        $application->update([
            'status'      => KycApplication::STATUS_APPROVED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'expires_at'  => now()->addYear(),
        ]);

        return $application->fresh();
    }

    public function reject(KycApplication $application, string $reviewerId, string $reason): KycApplication
    {
        $application->update([
            'status'              => KycApplication::STATUS_REJECTED,
            'reviewed_by'         => $reviewerId,
            'reviewed_at'         => now(),
            'rejection_reason_en' => $reason,
        ]);

        return $application->fresh();
    }
}
```

### File: `database/factories/InvestorProfileFactory.php`

```php
<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Investors\Models\InvestorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestorProfileFactory extends Factory
{
    protected $model = InvestorProfile::class;

    public function definition(): array
    {
        return [
            'user_id'             => User::factory(),
            'investor_type'       => 'individual',
            'accreditation_level' => 'retail',
            'national_id'         => $this->faker->numerify('##########'),
            'id_type'             => 'CNI',
            'dob'                 => $this->faker->date('Y-m-d', '2000-01-01'),
            'nationality'         => 'CM',
            'occupation'          => $this->faker->jobTitle(),
            'annual_income'       => $this->faker->numberBetween(1000000, 50000000),
            'net_worth'           => $this->faker->numberBetween(2000000, 200000000),
            'risk_tolerance'      => 'moderate',
            'is_pep'              => false,
            'is_sanctioned'       => false,
        ];
    }
}
```

> Add `database/factories/InvestmentPledgeFactory.php` too (used by D3-6 tests):

### File: `database/factories/InvestmentPledgeFactory.php`

```php
<?php

namespace Database\Factories;

use App\Modules\Auth\Models\User;
use App\Modules\Investors\Models\InvestmentPledge;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestmentPledgeFactory extends Factory
{
    protected $model = InvestmentPledge::class;

    public function definition(): array
    {
        return [
            'investor_id'      => User::factory(),
            'offering_id'      => ShareOffering::factory()->open(),
            'amount'           => $this->faker->numberBetween(10000, 5000000),
            'shares_requested' => $this->faker->numberBetween(1, 1000),
            'status'           => InvestmentPledge::STATUS_PENDING,
        ];
    }
}
```

### Provider: `app/Modules/Investors/Providers/InvestorsServiceProvider.php`

```php
<?php

namespace App\Modules\Investors\Providers;

use App\Modules\Investors\Services\InvestorService;
use App\Modules\Investors\Services\KycService;
use Illuminate\Support\ServiceProvider;

class InvestorsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(InvestorService::class);
        $this->app->singleton(KycService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
```

> Register `InvestorsServiceProvider` in `bootstrap/providers.php`. Create an
> empty `app/Modules/Investors/Routes/api.php` (filled in D3-5/D3-6/D3-7).

### Commit

```bash
git add app/Modules/Investors database/factories/InvestorProfileFactory.php database/factories/InvestmentPledgeFactory.php bootstrap/providers.php
git commit -m "feat(investors): profile, KYC, pledge models + investor/KYC services + factories"
```

---

# Task D3-5: Investor Profile + KYC Endpoints (TDD)

| Method | URI | Auth |
|--------|-----|------|
| GET | `/api/v1/investor/profile` | `investor:profile` |
| PUT | `/api/v1/investor/profile` | `investor:profile` |
| POST | `/api/v1/investor/kyc/submit` | `investor:profile` |
| GET | `/api/v1/investor/kyc/status` | `investor:profile` |
| POST | `/api/v1/investor/kyc/documents` | `investor:profile` |
| GET | `/api/v1/admin/kyc` | `role:cmf_reviewer` |
| POST | `/api/v1/admin/kyc/{id}/approve` | `role:cmf_reviewer` |
| POST | `/api/v1/admin/kyc/{id}/reject` | `role:cmf_reviewer` |

### STEP 1 — RED

### File: `tests/Feature/Investors/InvestorProfileTest.php`

```php
<?php

namespace Tests\Feature\Investors;

use App\Modules\Auth\Models\User;
use App\Modules\Investors\Models\KycApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class InvestorProfileTest extends TestCase
{
    use RefreshDatabase;

    private function investor(): User
    {
        $user = User::factory()->create();
        $user->assignRole('investor');

        return $user;
    }

    private function reviewer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('cmf_reviewer');

        return $user;
    }

    public function test_investor_can_fetch_their_profile(): void
    {
        Passport::actingAs($this->investor(), ['investor:profile']);

        $this->getJson('/api/v1/investor/profile')
            ->assertOk()
            ->assertJsonStructure(['data' => ['investor_type', 'accreditation_level', 'risk_tolerance']]);
    }

    public function test_fetching_profile_requires_investor_profile_scope(): void
    {
        Passport::actingAs($this->investor(), []);

        $this->getJson('/api/v1/investor/profile')->assertForbidden();
    }

    public function test_investor_can_update_their_profile(): void
    {
        Passport::actingAs($this->investor(), ['investor:profile']);

        $this->putJson('/api/v1/investor/profile', [
            'occupation'     => 'Engineer',
            'risk_tolerance' => 'aggressive',
            'nationality'    => 'CM',
        ])->assertOk()
          ->assertJsonPath('data.occupation', 'Engineer')
          ->assertJsonPath('data.risk_tolerance', 'aggressive');
    }

    public function test_investor_can_submit_kyc(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:profile']);

        $this->postJson('/api/v1/investor/kyc/submit', ['tier' => 'standard'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'submitted');

        $this->assertDatabaseHas('kyc_applications', [
            'user_id' => $investor->id, 'status' => 'submitted',
        ]);
    }

    public function test_investor_can_check_kyc_status(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:profile']);
        KycApplication::create([
            'user_id' => $investor->id, 'tier' => 'basic',
            'status' => 'submitted', 'submitted_at' => now(),
        ]);

        $this->getJson('/api/v1/investor/kyc/status')
            ->assertOk()
            ->assertJsonPath('data.status', 'submitted');
    }

    public function test_investor_can_upload_kyc_document(): void
    {
        Storage::fake('local');
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:profile']);
        KycApplication::create([
            'user_id' => $investor->id, 'tier' => 'basic',
            'status' => 'submitted', 'submitted_at' => now(),
        ]);

        $this->postJson('/api/v1/investor/kyc/documents', [
            'type' => 'national_id_front',
            'file' => UploadedFile::fake()->image('id.jpg'),
        ])->assertCreated();

        $this->assertDatabaseHas('kyc_documents', ['type' => 'national_id_front']);
    }

    public function test_reviewer_can_list_pending_kyc(): void
    {
        Passport::actingAs($this->reviewer(), []);
        KycApplication::create([
            'user_id' => $this->investor()->id, 'tier' => 'basic',
            'status' => 'submitted', 'submitted_at' => now(),
        ]);

        $this->getJson('/api/v1/admin/kyc')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'status', 'tier']], 'meta']);
    }

    public function test_reviewer_can_approve_kyc(): void
    {
        Passport::actingAs($this->reviewer(), []);
        $app = KycApplication::create([
            'user_id' => $this->investor()->id, 'tier' => 'basic',
            'status' => 'submitted', 'submitted_at' => now(),
        ]);

        $this->postJson("/api/v1/admin/kyc/{$app->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_reviewer_can_reject_kyc(): void
    {
        Passport::actingAs($this->reviewer(), []);
        $app = KycApplication::create([
            'user_id' => $this->investor()->id, 'tier' => 'basic',
            'status' => 'submitted', 'submitted_at' => now(),
        ]);

        $this->postJson("/api/v1/admin/kyc/{$app->id}/reject", ['reason' => 'Blurry ID.'])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');
    }

    public function test_non_reviewer_cannot_list_pending_kyc(): void
    {
        Passport::actingAs($this->investor(), ['investor:profile']);

        $this->getJson('/api/v1/admin/kyc')->assertForbidden();
    }
}
```

### STEP 2 — GREEN

### File: `app/Modules/Investors/Requests/UpdateInvestorProfileRequest.php`

```php
<?php

namespace App\Modules\Investors\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvestorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'investor_type'       => ['sometimes', Rule::in(['individual', 'institutional'])],
            'accreditation_level' => ['sometimes', Rule::in(['retail', 'qualified', 'institutional'])],
            'national_id'         => ['nullable', 'string', 'max:30'],
            'id_type'             => ['nullable', 'string', 'max:50'],
            'dob'                 => ['nullable', 'date'],
            'nationality'         => ['nullable', 'string', 'size:2', 'max:3'],
            'occupation'          => ['nullable', 'string', 'max:255'],
            'employer'            => ['nullable', 'string', 'max:255'],
            'annual_income'       => ['nullable', 'integer', 'min:0'],
            'net_worth'           => ['nullable', 'integer', 'min:0'],
            'risk_tolerance'      => ['sometimes', Rule::in(['conservative', 'moderate', 'aggressive'])],
            'bank_name'           => ['nullable', 'string', 'max:255'],
            'bank_account'        => ['nullable', 'string', 'max:255'],
            'bank_rib'            => ['nullable', 'string', 'max:255'],
        ];
    }
}
```

### File: `app/Modules/Investors/Requests/SubmitKycRequest.php`

```php
<?php

namespace App\Modules\Investors\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tier' => ['nullable', Rule::in(['basic', 'standard', 'enhanced'])],
        ];
    }
}
```

### File: `app/Modules/Investors/Requests/UploadKycDocumentRequest.php`

```php
<?php

namespace App\Modules\Investors\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadKycDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['national_id_front', 'national_id_back', 'passport', 'selfie', 'proof_of_address', 'bank_statement', 'other'])],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
```

### File: `app/Modules/Investors/Resources/InvestorProfileResource.php`

```php
<?php

namespace App\Modules\Investors\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvestorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'user_id'             => $this->user_id,
            'investor_type'       => $this->investor_type,
            'accreditation_level' => $this->accreditation_level,
            'id_type'             => $this->id_type,
            'dob'                 => optional($this->dob)->toDateString(),
            'nationality'         => $this->nationality,
            'occupation'          => $this->occupation,
            'employer'            => $this->employer,
            'annual_income'       => $this->annual_income !== null ? (int) $this->annual_income : null,
            'net_worth'           => $this->net_worth !== null ? (int) $this->net_worth : null,
            'risk_tolerance'      => $this->risk_tolerance,
            'is_pep'              => (bool) $this->is_pep,
            'bank_name'           => $this->bank_name,
            'created_at'          => optional($this->created_at)->toIso8601String(),
            'updated_at'          => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
```

### File: `app/Modules/Investors/Resources/KycApplicationResource.php`

```php
<?php

namespace App\Modules\Investors\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KycApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'user_id'             => $this->user_id,
            'tier'                => $this->tier,
            'status'              => $this->status,
            'rejection_reason_fr' => $this->rejection_reason_fr,
            'rejection_reason_en' => $this->rejection_reason_en,
            'submitted_at'        => optional($this->submitted_at)->toIso8601String(),
            'reviewed_at'         => optional($this->reviewed_at)->toIso8601String(),
            'expires_at'          => optional($this->expires_at)->toIso8601String(),
            'documents'           => $this->whenLoaded('documents'),
            'created_at'          => optional($this->created_at)->toIso8601String(),
        ];
    }
}
```

### File: `app/Modules/Investors/Controllers/InvestorProfileController.php`

```php
<?php

namespace App\Modules\Investors\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Investors\Requests\UpdateInvestorProfileRequest;
use App\Modules\Investors\Resources\InvestorProfileResource;
use App\Modules\Investors\Services\InvestorService;
use Illuminate\Http\Request;

class InvestorProfileController extends Controller
{
    public function __construct(private readonly InvestorService $investors)
    {
    }

    public function show(Request $request): InvestorProfileResource
    {
        return new InvestorProfileResource(
            $this->investors->profileFor($request->user()->id)
        );
    }

    public function update(UpdateInvestorProfileRequest $request): InvestorProfileResource
    {
        return new InvestorProfileResource(
            $this->investors->updateProfile($request->user()->id, $request->validated())
        );
    }
}
```

### File: `app/Modules/Investors/Controllers/KycController.php`

```php
<?php

namespace App\Modules\Investors\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Investors\Models\KycApplication;
use App\Modules\Investors\Requests\SubmitKycRequest;
use App\Modules\Investors\Requests\UploadKycDocumentRequest;
use App\Modules\Investors\Resources\KycApplicationResource;
use App\Modules\Investors\Services\KycService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function __construct(private readonly KycService $kyc)
    {
    }

    public function submit(SubmitKycRequest $request): JsonResponse
    {
        $application = $this->kyc->submit($request->user()->id, $request->validated());

        return (new KycApplicationResource($application))->response()->setStatusCode(201);
    }

    public function status(Request $request): JsonResponse
    {
        $application = $this->kyc->currentApplication($request->user()->id);

        abort_if($application === null, 404, 'No KYC application found.');

        return (new KycApplicationResource($application))->response();
    }

    public function uploadDocument(UploadKycDocumentRequest $request): JsonResponse
    {
        $application = $this->kyc->currentApplication($request->user()->id);

        abort_if($application === null, 404, 'Submit a KYC application before uploading documents.');

        $document = $this->kyc->addDocument(
            $application,
            $request->file('file'),
            $request->validated()['type']
        );

        return response()->json(['data' => [
            'id' => $document->id, 'type' => $document->type,
        ]], 201);
    }

    // --- Compliance (cmf_reviewer) ---

    public function index(): JsonResponse
    {
        return KycApplicationResource::collection($this->kyc->pending())->response();
    }

    public function approve(Request $request, int $id): KycApplicationResource
    {
        $application = KycApplication::findOrFail($id);

        return new KycApplicationResource($this->kyc->approve($application, $request->user()->id));
    }

    public function reject(Request $request, int $id): KycApplicationResource
    {
        $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $application = KycApplication::findOrFail($id);

        return new KycApplicationResource(
            $this->kyc->reject($application, $request->user()->id, $request->input('reason'))
        );
    }
}
```

### File: `app/Modules/Investors/Routes/api.php`

```php
<?php

use App\Modules\Investors\Controllers\InvestorProfileController;
use App\Modules\Investors\Controllers\KycController;
use App\Modules\Investors\Controllers\PledgeController;
use App\Modules\Investors\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['api', 'auth:api'])->group(function () {
    // Investor self-service profile + KYC
    Route::middleware('scopes:investor:profile')->group(function () {
        Route::get('investor/profile', [InvestorProfileController::class, 'show']);
        Route::put('investor/profile', [InvestorProfileController::class, 'update']);
        Route::post('investor/kyc/submit', [KycController::class, 'submit']);
        Route::get('investor/kyc/status', [KycController::class, 'status']);
        Route::post('investor/kyc/documents', [KycController::class, 'uploadDocument']);
    });

    // Pledges + portfolio (D3-6 / D3-7)
    Route::middleware('scopes:investor:pledge')->group(function () {
        Route::post('offerings/{id}/pledges', [PledgeController::class, 'store']);
        Route::delete('offerings/{id}/pledges/{pledgeId}', [PledgeController::class, 'destroy']);
    });
    Route::middleware('scopes:investor:portfolio')->group(function () {
        Route::get('investor/pledges', [PledgeController::class, 'mine']);
        Route::get('investor/portfolio', [PortfolioController::class, 'show']);
    });

    // Compliance (cmf_reviewer) — KYC + offering pledge listing
    Route::middleware('role:cmf_reviewer|super_admin')->group(function () {
        Route::get('admin/kyc', [KycController::class, 'index']);
        Route::post('admin/kyc/{id}/approve', [KycController::class, 'approve']);
        Route::post('admin/kyc/{id}/reject', [KycController::class, 'reject']);
    });

    // Offering pledge listing — company owner / admin
    Route::get('offerings/{id}/pledges', [PledgeController::class, 'index']);
});
```

Run until green:

```bash
php artisan test --filter=InvestorProfileTest
```

### Commit

```bash
git add app/Modules/Investors tests/Feature/Investors/InvestorProfileTest.php
git commit -m "feat(investors): profile + KYC submit/status/upload + compliance review (TDD)"
```

---

# Task D3-6: Investment Pledge Endpoints (TDD)

| Method | URI | Auth |
|--------|-----|------|
| POST | `/api/v1/offerings/{id}/pledges` | `investor:pledge` |
| GET | `/api/v1/offerings/{id}/pledges` | auth (company owner / admin) |
| GET | `/api/v1/investor/pledges` | `investor:portfolio` |
| DELETE | `/api/v1/offerings/{id}/pledges/{pledgeId}` | `investor:pledge` (owner) |

Routes already declared in the Investors `api.php` above.

### STEP 1 — RED

### File: `tests/Feature/Investors/PledgeTest.php`

```php
<?php

namespace Tests\Feature\Investors;

use App\Modules\Auth\Models\User;
use App\Modules\Directory\Models\Company;
use App\Modules\Investors\Models\InvestmentPledge;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PledgeTest extends TestCase
{
    use RefreshDatabase;

    private function investor(): User
    {
        $user = User::factory()->create();
        $user->assignRole('investor');

        return $user;
    }

    public function test_investor_can_make_a_pledge(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create([
            'share_price' => 5000, 'min_investment' => 10000,
        ]);

        $this->postJson("/api/v1/offerings/{$offering->id}/pledges", ['amount' => 50000])
            ->assertCreated()
            ->assertJsonPath('data.amount', 50000)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('investment_pledges', [
            'offering_id' => $offering->id, 'investor_id' => $investor->id,
        ]);
    }

    public function test_pledge_requires_investor_pledge_scope(): void
    {
        Passport::actingAs($this->investor(), []);
        $offering = ShareOffering::factory()->open()->create();

        $this->postJson("/api/v1/offerings/{$offering->id}/pledges", ['amount' => 50000])
            ->assertForbidden();
    }

    public function test_pledge_below_minimum_is_rejected(): void
    {
        Passport::actingAs($this->investor(), ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create(['min_investment' => 100000]);

        $this->postJson("/api/v1/offerings/{$offering->id}/pledges", ['amount' => 5000])
            ->assertStatus(422);
    }

    public function test_cannot_pledge_to_a_non_open_offering(): void
    {
        Passport::actingAs($this->investor(), ['investor:pledge']);
        $offering = ShareOffering::factory()->create(['status' => 'draft']);

        $this->postJson("/api/v1/offerings/{$offering->id}/pledges", ['amount' => 50000])
            ->assertStatus(422);
    }

    public function test_investor_can_list_their_own_pledges(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:portfolio']);
        InvestmentPledge::factory()->count(2)->create(['investor_id' => $investor->id]);
        InvestmentPledge::factory()->create(); // someone else's

        $this->getJson('/api/v1/investor/pledges')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_company_owner_can_list_pledges_for_their_offering(): void
    {
        $company = Company::factory()->create();
        $owner = User::factory()->create();
        $owner->assignRole('company_owner');
        $company->members()->create([
            'user_id' => $owner->id, 'role' => 'owner', 'is_active' => true, 'joined_at' => now(),
        ]);
        Passport::actingAs($owner, ['companies:write']);

        $offering = ShareOffering::factory()->open()->create(['company_id' => $company->id]);
        InvestmentPledge::factory()->count(3)->create(['offering_id' => $offering->id]);

        $this->getJson("/api/v1/offerings/{$offering->id}/pledges")
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_investor_can_cancel_their_pending_pledge(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create();
        $pledge = InvestmentPledge::factory()->create([
            'investor_id' => $investor->id, 'offering_id' => $offering->id, 'status' => 'pending',
        ]);

        $this->deleteJson("/api/v1/offerings/{$offering->id}/pledges/{$pledge->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_investor_cannot_cancel_anothers_pledge(): void
    {
        Passport::actingAs($this->investor(), ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create();
        $pledge = InvestmentPledge::factory()->create([
            'offering_id' => $offering->id, 'status' => 'pending',
        ]);

        $this->deleteJson("/api/v1/offerings/{$offering->id}/pledges/{$pledge->id}")
            ->assertStatus(422);
    }
}
```

### STEP 2 — GREEN

### File: `app/Modules/Investors/Requests/CreatePledgeRequest.php`

```php
<?php

namespace App\Modules\Investors\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePledgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount'           => ['required', 'integer', 'min:1'],
            'shares_requested' => ['nullable', 'integer', 'min:1'],
            'payment_method'   => ['nullable', Rule::in(['mtn_momo', 'orange_money', 'bank_transfer'])],
        ];
    }
}
```

### File: `app/Modules/Investors/Resources/PledgeResource.php`

```php
<?php

namespace App\Modules\Investors\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PledgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'investor_id'          => $this->investor_id,
            'offering_id'          => $this->offering_id,
            'amount'               => (int) $this->amount,
            'shares_requested'     => $this->shares_requested !== null ? (int) $this->shares_requested : null,
            'status'               => $this->status,
            'payment_method'       => $this->payment_method,
            'payment_reference'    => $this->payment_reference,
            'payment_confirmed_at' => optional($this->payment_confirmed_at)->toIso8601String(),
            'expires_at'           => optional($this->expires_at)->toIso8601String(),
            'created_at'           => optional($this->created_at)->toIso8601String(),
        ];
    }
}
```

### File: `app/Modules/Investors/Controllers/PledgeController.php`

```php
<?php

namespace App\Modules\Investors\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Investors\Models\InvestmentPledge;
use App\Modules\Investors\Requests\CreatePledgeRequest;
use App\Modules\Investors\Resources\PledgeResource;
use App\Modules\Investors\Services\InvestorService;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PledgeController extends Controller
{
    public function __construct(private readonly InvestorService $investors)
    {
    }

    public function store(CreatePledgeRequest $request, string $id): JsonResponse
    {
        $offering = ShareOffering::findOrFail($id);

        $pledge = $this->investors->pledge($offering, $request->user()->id, $request->validated());

        return (new PledgeResource($pledge))->response()->setStatusCode(201);
    }

    /** Pledges for one offering — company owner or admin only. */
    public function index(Request $request, string $id): JsonResponse
    {
        $offering = ShareOffering::with('company')->findOrFail($id);

        $isAdmin = $request->user()->hasAnyRole(['super_admin', 'cmf_reviewer']);
        $isOwner = $offering->company?->members()
            ->where('user_id', $request->user()->id)
            ->where('role', 'owner')
            ->exists();

        abort_unless($isAdmin || $isOwner, 403, 'Not authorized to view pledges for this offering.');

        return PledgeResource::collection(
            $this->investors->pledgesForOffering($offering)
        )->response();
    }

    /** The authenticated investor's own pledges. */
    public function mine(Request $request): JsonResponse
    {
        return PledgeResource::collection(
            $this->investors->pledgesForInvestor($request->user()->id)
        )->response();
    }

    public function destroy(Request $request, string $id, string $pledgeId): JsonResponse
    {
        $pledge = InvestmentPledge::where('offering_id', $id)->findOrFail($pledgeId);

        $pledge = $this->investors->cancelPledge($pledge, $request->user()->id);

        return (new PledgeResource($pledge))->response();
    }
}
```

> The `GET /offerings/{id}/pledges` route is registered (outside the scope
> groups) in the Investors `api.php` shown in D3-5. It only needs `auth:api`;
> ownership is enforced in the controller.

Run until green:

```bash
php artisan test --filter=PledgeTest
```

### Commit

```bash
git add app/Modules/Investors tests/Feature/Investors/PledgeTest.php
git commit -m "feat(investors): investment pledges — create/list/cancel (TDD)"
```

---

# Task D3-7: Portfolio Endpoint

| Method | URI | Auth |
|--------|-----|------|
| GET | `/api/v1/investor/portfolio` | `investor:portfolio` |

Route already declared in the Investors `api.php` (D3-5).

### STEP 1 — RED

### File: `tests/Feature/Investors/PortfolioTest.php`

```php
<?php

namespace Tests\Feature\Investors;

use App\Modules\Auth\Models\User;
use App\Modules\Investors\Models\InvestmentPledge;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    private function investor(): User
    {
        $user = User::factory()->create();
        $user->assignRole('investor');

        return $user;
    }

    public function test_investor_can_view_portfolio_summary(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:portfolio']);

        $offering = ShareOffering::factory()->open()->create();
        InvestmentPledge::factory()->create([
            'investor_id' => $investor->id, 'offering_id' => $offering->id,
            'amount' => 250000, 'status' => 'payment_received',
        ]);

        $this->getJson('/api/v1/investor/portfolio')
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'total_invested', 'total_pledged', 'current_value',
                'total_dividends_received', 'companies_count', 'active_pledges', 'pledges',
            ]])
            ->assertJsonPath('data.total_pledged', 250000)
            ->assertJsonPath('data.active_pledges', 1);
    }

    public function test_portfolio_requires_investor_portfolio_scope(): void
    {
        Passport::actingAs($this->investor(), []);

        $this->getJson('/api/v1/investor/portfolio')->assertForbidden();
    }

    public function test_empty_portfolio_returns_zeroes(): void
    {
        Passport::actingAs($this->investor(), ['investor:portfolio']);

        $this->getJson('/api/v1/investor/portfolio')
            ->assertOk()
            ->assertJsonPath('data.total_pledged', 0)
            ->assertJsonPath('data.active_pledges', 0);
    }
}
```

### STEP 2 — GREEN

### File: `app/Modules/Investors/Resources/PortfolioResource.php`

```php
<?php

namespace App\Modules\Investors\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{
    /** @var array $resource is the summary array from InvestorService::portfolioSummary. */
    public function toArray(Request $request): array
    {
        return [
            'total_invested'           => (int) $this->resource['total_invested'],
            'total_pledged'            => (int) $this->resource['total_pledged'],
            'current_value'            => (int) $this->resource['current_value'],
            'total_dividends_received' => (int) $this->resource['total_dividends_received'],
            'companies_count'          => (int) $this->resource['companies_count'],
            'active_pledges'           => (int) $this->resource['active_pledges'],
            'pledges'                  => PledgeResource::collection($this->resource['pledges']),
        ];
    }
}
```

### File: `app/Modules/Investors/Controllers/PortfolioController.php`

```php
<?php

namespace App\Modules\Investors\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Investors\Resources\PortfolioResource;
use App\Modules\Investors\Services\InvestorService;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function __construct(private readonly InvestorService $investors)
    {
    }

    public function show(Request $request): PortfolioResource
    {
        return new PortfolioResource(
            $this->investors->portfolioSummary($request->user()->id)
        );
    }
}
```

Run until green:

```bash
php artisan test --filter=PortfolioTest
```

### Commit

```bash
git add app/Modules/Investors tests/Feature/Investors/PortfolioTest.php
git commit -m "feat(investors): portfolio summary endpoint (TDD)"
```

---

# Task D3-8: Run all tests + merge

### Run the full suite

```bash
php artisan test
```

Expected new tests (added in Day 3):

- `OfferingTest` — 14 tests
- `OrderBookTest` — 6 tests
- `InvestorProfileTest` — 10 tests
- `PledgeTest` — 8 tests
- `PortfolioTest` — 3 tests

Plus all Day 1 (31) + Day 2 suites must still pass. If anything is red, fix
before merging — **do not merge on red.**

### Pre-merge checklist

- [ ] `TradingServiceProvider` + `InvestorsServiceProvider` registered in
      `bootstrap/providers.php`.
- [ ] `RuntimeException → 422` render hook present in `bootstrap/app.php`.
- [ ] Meilisearch index `share_offerings` configured in `config/scout.php`
      (filterable: `status`, `company_id`, `instrument_type`). Run
      `php artisan scout:sync-index-settings` if using Meilisearch v1 settings,
      else import: `php artisan scout:import "App\Modules\Trading\Models\ShareOffering"`.
- [ ] New OAuth scopes (`investor:profile`, `investor:pledge`,
      `investor:portfolio`) exist in the Passport scope registration from Day 1.
      If missing, add them in `App\Providers\AuthServiceProvider` /
      `AppServiceProvider::boot()` `Passport::tokensCan([...])`.
- [ ] `php artisan route:list --path=api/v1` shows all new routes.

### Merge & branch

```bash
git checkout master
git merge feat/day2-directory
git checkout -b feat/day3-trading
```

> **Sequencing note:** The brief's commands merge `feat/day2-directory` into
> `master` first, then cut `feat/day3-trading` from there. Day 3 work should be
> committed onto `feat/day3-trading`. If you have been committing D3-1..D3-7 on
> `feat/day2-directory`, instead run the merge first, then cut the new branch —
> the Day 3 commits travel with the merge. Recommended flow:
> ```bash
> # while on feat/day2-directory with D3 commits already made:
> git checkout master
> git merge feat/day2-directory          # fast-forwards D3 work into master
> git checkout -b feat/day3-trading      # cut Day 3 branch for any follow-ups
> php artisan test                        # final green confirmation
> ```

### Final commit (branch bookkeeping, if needed)

```bash
git add -A
git commit -m "chore: Day 3 complete — Trading + Investors modules merged"
```

---

## Day 3 Definition of Done

- Trading module: 5 models, 2 services, 2 controllers, offering CRUD + CMF
  workflow + order book — **20 feature tests green**.
- Investors module: 4 models, 2 services, 4 controllers, profile + KYC + pledges
  + portfolio — **21 feature tests green**.
- ~41 new feature tests added; entire suite green.
- Both module ServiceProviders registered; routes under `api/v1/` resolve.
- New scopes wired; `share_offerings` searchable via Meilisearch.
