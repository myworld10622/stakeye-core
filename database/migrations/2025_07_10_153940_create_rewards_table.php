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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('deposit');
            $table->decimal('min_deposit_amount', 15, 2)->nullable();
            $table->decimal('max_deposit_amount', 15, 2)->nullable();
            $table->string('reward_type')->default('fixed');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('conversion_type')->default('bet');//bet and pnl 
            $table->decimal('max_amount_allowed', 15, 2);            
            $table->integer('timeline_in_days');
            $table->decimal('pnl_required_amount', 15, 2);
            $table->decimal('pnl_required_multiplier', 8, 4);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
