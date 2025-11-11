<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates indexes carefully using prefix lengths for string columns
     * to avoid "Specified key was too long" errors on utf8mb4 tables.
     */
    public function up(): void
    {
        // Get existing index names
        $existing = collect(DB::select("SHOW INDEX FROM `sports_bets_history`"))
            ->pluck('Key_name')
            ->map(fn($v) => (string) $v)
            ->all();

        // Create the UNIQUE if not present and no duplicates
        if (! in_array('uq_sports_tx_method', $existing, true)) {
            $duplicates = DB::select("
                SELECT 1 FROM (
                    SELECT transactionId, methodName
                    FROM sports_bets_history
                    GROUP BY transactionId, methodName
                    HAVING COUNT(*) > 1
                    LIMIT 1
                ) t
            ");
            if (empty($duplicates)) {
                Schema::table('sports_bets_history', function (Blueprint $table) {
                    $table->unique(['transactionId', 'methodName'], 'uq_sports_tx_method');
                });
            } else {
                // skip creating unique if duplicates exists; ops should dedupe first
            }
        }

        // idx_sports_partner_user
        if (! in_array('idx_sports_partner_user', $existing, true)) {
            // partnerId (likely numeric) + userName (string) -> safe to create via Schema
            Schema::table('sports_bets_history', function (Blueprint $table) {
                $table->index(['partnerId', 'userName'], 'idx_sports_partner_user');
            });
        }

        // idx_sports_transactionId
        if (! in_array('idx_sports_transactionId', $existing, true)) {
            Schema::table('sports_bets_history', function (Blueprint $table) {
                $table->index('transactionId', 'idx_sports_transactionId');
            });
        }

        // idx_sports_partner_market_user (composite includes potentially long varchar columns).
        // We create this with prefix lengths for string columns to avoid "key too long".
        if (! in_array('idx_sports_partner_market_user', $existing, true)) {
            // Choose reasonable prefix lengths:
            // - partnerId: typically numeric (no prefix)
            // - marketId: use prefix 191 (safe for varchar(255) on utf8mb4)
            // - userName: use prefix 191
            // Adjust prefix lengths if you know these columns are shorter.
            try {
                DB::statement("
                    ALTER TABLE `sports_bets_history`
                    ADD INDEX `idx_sports_partner_market_user` (`partnerId`, `marketId`(191), `userName`(191))
                ");
            } catch (\Throwable $e) {
                // If it still fails, log to laravel log and continue (so migration doesn't kill itself).
                // You should inspect the exception in logs and adjust prefix lengths or column types.
                \Log::warning('Failed to create idx_sports_partner_market_user: ' . $e->getMessage());
            }
        }

        // idx_sports_methodName
        if (! in_array('idx_sports_methodName', $existing, true)) {
            // single-column index on methodName, safe if methodName not too long; use prefix if needed:
            try {
                DB::statement("
                    ALTER TABLE `sports_bets_history`
                    ADD INDEX `idx_sports_methodName` (`methodName`(191))
                ");
            } catch (\Throwable $e) {
                // fallback: try without prefix (or log and continue)
                try {
                    DB::statement("
                        ALTER TABLE `sports_bets_history`
                        ADD INDEX `idx_sports_methodName` (`methodName`)
                    ");
                } catch (\Throwable $inner) {
                    \Log::warning('Failed to create idx_sports_methodName: ' . $inner->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sports_bets_history', function (Blueprint $table) {
            // Wrap in try/catch to avoid exceptions if missing
            try { $table->dropUnique('uq_sports_tx_method'); } catch (\Throwable $e) {}
            try { $table->dropIndex('idx_sports_partner_user'); } catch (\Throwable $e) {}
            try { $table->dropIndex('idx_sports_transactionId'); } catch (\Throwable $e) {}
            try { DB::statement("ALTER TABLE `sports_bets_history` DROP INDEX `idx_sports_partner_market_user`"); } catch (\Throwable $e) {}
            try { DB::statement("ALTER TABLE `sports_bets_history` DROP INDEX `idx_sports_methodName`"); } catch (\Throwable $e) {}
        });
    }
};
