<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The original scaffold shipped an unused guest-enquiry table under this
        // name (no model, controller or route ever referenced it). The real
        // quotation system replaces it.
        Schema::dropIfExists('quote_requests');

        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->nullable();
            // users.id is a UUID (same convention as conversations.buyer_id)
            $table->uuid('buyer_id')->index();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained('conversations')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('message')->nullable();
            $table->date('desired_response_date')->nullable();
            $table->string('status')->default('pending'); // pending|quoted|negotiation|accepted|refused|expired
            $table->timestamps();
        });

        Schema::create('quote_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->nullable();
            $table->foreignId('quote_request_id')->constrained('quote_requests')->cascadeOnDelete();
            $table->unsignedTinyInteger('version')->default(1);
            $table->string('status')->default('draft'); // draft|sent|accepted|refused|expired
            $table->string('currency', 8)->default('FCFA');
            $table->string('incoterms')->nullable();
            $table->string('delivery_location')->nullable();
            $table->string('production_delay')->nullable();
            $table->string('delivery_delay')->nullable();
            $table->string('payment_terms')->nullable();
            $table->decimal('global_discount_pct', 5, 2)->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('delivery_fee')->default(0);
            $table->unsignedBigInteger('insurance_fee')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('quote_proposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_proposal_id')->constrained('quote_proposals')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit')->default('Pièces');
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->nullable();
            $table->foreignId('quote_proposal_id')->constrained('quote_proposals')->cascadeOnDelete();
            $table->string('status')->default('created'); // created|confirmed|in_production|shipped|delivered|cancelled
            $table->unsignedBigInteger('total')->default(0);
            $table->date('expected_delivery_date')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->nullable();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->string('status')->default('unpaid'); // unpaid|paid
            $table->unsignedBigInteger('total')->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('quote_proposal_items');
        Schema::dropIfExists('quote_proposals');
        Schema::dropIfExists('quote_requests');
    }
};
