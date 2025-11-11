<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use Illuminate\Http\Request;
use Laramin\Utility\Helpmate;
use Laramin\Utility\VugiChugi;

Route::get('/', function (Request $request) {
    $status = Helpmate::sysPass();

    // If not active, show activation UI directly (avoid redirect)
    if ($status !== true && $status !== 99) {
        $pageTitle = VugiChugi::lsTitle();
        // try vendor view (same name used by package)
        if (view()->exists('Utility::laramin_start')) {
            return view('Utility::laramin_start', compact('pageTitle'));
        }
        return response('Activation required.', 200);
    }

    // Otherwise, call site's home controller (keeps original behavior)
    return app()->call(\App\Http\Controllers\SiteController::class . '@home');
})->name('home.override');

Route::get('/clear', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});

Route::get('cron', 'CronController@cron')->name('cron');

// Route::get('livecasino', 'SiteController@liveCasino')->name('liveCasino');
Route::get('activate', [\Laramin\Utility\Controller\UtilityController::class, 'laraminStart'])
    ->name('activate')
    ->middleware('web');

Route::post('activate_system_submit', [\Laramin\Utility\Controller\UtilityController::class, 'laraminSubmit'])
    ->name('activate_system_submit')
    ->middleware('web');
Route::get('livecasino', 'SiteController@newfunGame')->name('newfunGame');
Route::get('/games/search', 'SiteController@searchGame')->name('searchGame');
Route::get('/load-more', 'SiteController@loadMore')->name('loadMore');
Route::get('number-result-cron', 'CronController@numberResultCron')->name('number-result-cron');
Route::get('process-bonus-rewards', 'CronController@processBonusRewards')->name('process-bonus-rewards');


Route::get('sports', 'User\UserController@sportsGame')->name('sports');
Route::get('rungame/{url}', 'User\UserController@runGame')->name('rungame');
Route::get('setup-game/{gameid}/{gameTableId}', 'User\UserController@setupGame')->name('setupGame');

Route::get('trending-games', 'SiteController@trendingGames')->name('trendingGames');
// User Support Ticket
Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
    Route::get('/', 'supportTicket')->name('index');
    Route::get('new', 'openSupportTicket')->name('open');
    Route::post('create', 'storeSupportTicket')->name('store');
    Route::get('view/{ticket}', 'viewTicket')->name('view');
    Route::post('reply/{id}', 'replyTicket')->name('reply');
    Route::post('close/{id}', 'closeTicket')->name('close');
    Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
});

Route::controller('SiteController')->group(function () {
    Route::get('/lottery-home', 'lotteryHome')->name('lottery.home');
    Route::get('/fungame', 'funGame')->name('fun.game');
    Route::get('/get-games', 'getGames')->name('get-games');
    Route::get('/providers/{provider}', 'providerList')->name('provider-list');

    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactSubmit');
    Route::get('/change/{lang?}', 'changeLanguage')->name('lang');
    
    Route::get('cookie-policy', 'cookiePolicy')->name('cookie.policy');
    
    Route::get('/cookie/accept', 'cookieAccept')->name('cookie.accept');
    
    Route::get('blogs', 'blogs')->name('blog');
    Route::get('blog/{slug}', 'blogDetails')->name('blog.details');
    
    Route::get('policy/{slug}', 'policyPages')->name('policy.pages');

    Route::get('placeholder-image/{size}', 'placeholderImage')->name('placeholder.image');
    Route::get('maintenance-mode', 'maintenance')->withoutMiddleware('maintenance')->name('maintenance');
    
    Route::get('lottery', 'lottery')->name('lottery');
    Route::get('details/{id}', 'lotteryDetails')->name('lottery.details');
    Route::post('subscribe', 'subscribe')->name('subscribe');
    
    Route::get('/{slug}', 'pages')->name('pages');
    Route::get('/', 'home')->name('home');
});

Route::controller('User\Auth\LoginController')->group(function () {
    Route::get('/access-account/{id}', 'autoLogin')->name('autoLogin');
});
