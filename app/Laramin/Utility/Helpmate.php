<?php

namespace Laramin\Utility;

use App\Models\GeneralSetting;

class Helpmate
{
     public static function sysPass()
    {
        try {
            // 1) Locate laramin.json using base_path so path resolution is robust.
            $jsonPath = base_path('vendor/laramin/utility/src/laramin.json');
            $fileExists = file_exists($jsonPath);

            // 2) Load GeneralSetting (if present) — cached short term to avoid DB hits
            $general = cache()->get('GeneralSetting');
            if (!$general) {
                $general = GeneralSetting::first();
                if ($general) {
                    cache()->put('GeneralSetting', $general, now()->addMinutes(10));
                }
            }

            // 3) Purchase code from cache or environment
            $hasPurchaseCode = cache()->get('purchase_code');
            if (!$hasPurchaseCode) {
                $hasPurchaseCode = env('PURCHASECODE') ?: null;
                cache()->put('purchase_code', $hasPurchaseCode, now()->addMinutes(10));
            }

            // If either the JSON file or purchase code is missing, not activated
            if (!$fileExists || empty($hasPurchaseCode)) {
                if (function_exists('logger')) {
                    logger()->debug('Helpmate::sysPass negative check', [
                        'fileExists' => $fileExists,
                        'env_purchasecode' => substr((string)$hasPurchaseCode, 0, 6) . '...',
                        'jsonPath' => $jsonPath,
                    ]);
                }
                return false;
            }

            // 4) If laramin.json exists, prefer checking that its purchase_code matches env PURCHASECODE
            $json = @json_decode(@file_get_contents($jsonPath), true);
            if (is_array($json) && !empty($json['purchase_code'])) {
                if ($json['purchase_code'] === $hasPurchaseCode) {
                    // OK — local JSON and env match: consider activated
                    // But still check maintenance flag if present
                } else {
                    if (function_exists('logger')) {
                        logger()->warning('Helpmate::sysPass purchase_code mismatch', [
                            'env' => substr((string)$hasPurchaseCode, 0, 6) . '...',
                            'json' => substr((string)$json['purchase_code'], 0, 6) . '...',
                        ]);
                    }
                    // mismatch — treat as not activated
                    return false;
                }
            } else {
                // json missing or malformed — not activated
                if (function_exists('logger')) {
                    logger()->warning('Helpmate::sysPass laramin.json missing purchase_code or malformed', [
                        'jsonPath' => $jsonPath,
                        'jsonContents' => $json,
                    ]);
                }
                return false;
            }

            // 5) SAFEGUARD: check maintenance_mode if general settings exist
            $maintenanceMode = 0;
            if (is_object($general) && (property_exists($general, 'maintenance_mode') || isset($general->maintenance_mode))) {
                $maintenanceMode = (int) $general->maintenance_mode;
            } else {
                if (function_exists('logger')) {
                    logger()->warning('Helpmate: GeneralSetting missing or lacks maintenance_mode property.', ['general' => $general]);
                }
            }

            if ($maintenanceMode === 9) {
                return 99;
            }

            // If we reached here, local verification succeeded and we're not in special maintenance
            return true;
        } catch (\Throwable $e) {
            // On unexpected error, log and return false (conservative)
            if (function_exists('logger')) {
                logger()->error('Helpmate::sysPass exception: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
            return false;
        }
    }

    public static function appUrl()
    {
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http');
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';

        return $scheme . '://' . $host . $requestUri;
    }
}
