<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->string('lineclear_waybill_no')->nullable()->after('order_number');
            $table->string('shipment_status')->nullable()->after('fulfillment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            if (Schema::hasColumn('shopify_orders', 'lineclear_waybill_no')) {
                $table->dropColumn('lineclear_waybill_no');
            }

            if (Schema::hasColumn('shopify_orders', 'shipment_status')) {
                $table->dropColumn('shipment_status');
            }
        });
    }
};
