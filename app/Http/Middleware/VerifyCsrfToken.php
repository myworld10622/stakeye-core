<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{

    protected $except = [
        // Temporary: allow laramin activation POST without CSRF while debugging activation loop.
        // Remove this entry after activation or replace with a more secure approach.
        'activate_system_submit',
        // or use wildcard:
        // 'activate*',
    ];
    
}
