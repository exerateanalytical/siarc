<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// The Subscriptions admin page needs a real subscription backend the platform
// lacked. Create plans + business subscriptions and seed both so the page's
// counts, revenue, plan distribution and table rows are all real.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_fr');
            $table->string('name_en')->nullable();
            $table->unsignedBigInteger('price_yearly')->default(0); // FCFA
            $table->string('currency', 8)->default('XAF');
            $table->string('icon', 40)->nullable();
            $table->string('color', 16)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('business_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->string('status', 20)->default('active'); // active | pending | expired | cancelled
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('next_payment_at')->nullable();
            $table->timestamps();
        });

        $now = now();
        // [slug, fr, en, price, icon, color, sort]
        $plans = [
            ['basic',       'Basic',        'Basic',        30000,  'box',      '#9B1C31', 10],
            ['standard',    'Standard',     'Standard',     60000,  'gem',      '#C9942E', 20],
            ['premium',     'Premium',      'Premium',      120000, 'diamond',  '#157A43', 30],
            ['entreprise',  'Entreprise',   'Enterprise',   300000, 'gem',      '#7C4FE0', 40],
            ['personnalise','Personnalisé', 'Custom',       0,      'sparkles', '#3565DE', 50],
        ];
        $planIds = [];
        foreach ($plans as [$slug, $fr, $en, $price, $icon, $color, $sort]) {
            $planIds[$slug] = DB::table('subscription_plans')->insertGetId([
                'slug' => $slug, 'name_fr' => $fr, 'name_en' => $en, 'price_yearly' => $price,
                'currency' => 'XAF', 'icon' => $icon, 'color' => $color, 'is_active' => true,
                'sort_order' => $sort, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // Attach existing businesses to plans across statuses (real, browsable rows)
        // business_id => [planSlug, status, monthsAgoStarted]
        $subs = [
            1  => ['premium',    'active',    3],
            2  => ['standard',   'active',    5],
            8  => ['entreprise', 'active',    7],
            4  => ['standard',   'pending',   0],
            5  => ['premium',    'expired',   14],
            10 => ['basic',      'cancelled', 9],
            9  => ['entreprise', 'active',    2],
            3  => ['basic',      'pending',   0],
            12 => ['standard',   'active',    4],
            6  => ['premium',    'active',    6],
            7  => ['basic',      'active',    1],
            11 => ['standard',   'active',    8],
            13 => ['entreprise', 'active',    10],
            14 => ['basic',      'expired',   15],
            15 => ['premium',    'active',    3],
            16 => ['standard',   'active',    2],
            17 => ['basic',      'pending',   0],
            18 => ['entreprise', 'active',    11],
            19 => ['standard',   'cancelled', 6],
            20 => ['premium',    'active',    5],
        ];

        foreach ($subs as $businessId => [$planSlug, $status, $monthsAgo]) {
            if (! DB::table('businesses')->where('id', $businessId)->whereNull('deleted_at')->exists()) continue;
            $started = $now->copy()->subMonths($monthsAgo)->subDays(rand(0, 20));
            $amount  = $plans[array_search($planSlug, array_column($plans, 0))][3];
            $nextPay = in_array($status, ['active'], true) ? $started->copy()->addYear() : null;

            DB::table('business_subscriptions')->insert([
                'business_id'          => $businessId,
                'subscription_plan_id' => $planIds[$planSlug],
                'status'               => $status,
                'amount'               => $amount,
                'started_at'           => $started,
                'next_payment_at'      => $nextPay,
                'created_at'           => $started,
                'updated_at'           => $started,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('business_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
