<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->decimal('vat_rate', 5, 2)->default(0)->after('customer_name');
            $table->decimal('vat_amount', 10, 2)->default(0)->after('vat_rate');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('vat_amount');
            $table->decimal('subtotal', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('total', 10, 2)->default(0)->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'vat_rate',
                'vat_amount',
                'discount_amount',
                'subtotal',
                'total'
            ]);
        });
    }
};
