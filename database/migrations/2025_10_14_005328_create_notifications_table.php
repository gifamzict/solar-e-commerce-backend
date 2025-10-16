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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_preorder_id')->constrained('customer_pre_orders')->onDelete('cascade');
            $table->enum('mode', ['ready', 'balance']);
            $table->json('channels'); // Original requested channels
            $table->string('subject');
            $table->text('message_template'); // Original text with tags
            $table->text('message_resolved_email')->nullable(); // Resolved body for email
            $table->text('message_resolved_sms')->nullable(); // Resolved body for SMS
            $table->date('payment_deadline')->nullable();
            $table->text('reason')->nullable();
            $table->date('ready_date')->nullable();
            $table->enum('fulfillment_method', ['pickup', 'delivery']);
            $table->text('pickup_location')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->foreignId('created_by_admin_id')->constrained('admins')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
