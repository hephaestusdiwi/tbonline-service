<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SliderController;
use App\Http\Controllers\Api\NavigationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\RajaOngkirController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PromoCodeController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SalesReportController;
use App\Http\Controllers\Api\ProductReportController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\OrderRevisionController;
use App\Http\Controllers\Api\OlseraWebhookController;
use App\Http\Controllers\Api\OrderDeleteRequestController;
use App\Http\Controllers\Api\V1\Chat;
use App\Http\Controllers\Api\V1\Agent;
use App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Api\TopProductsController;
use App\Http\Controllers\Api\HomepageSectionController;
use App\Http\Controllers\Api\FooterLinkController;
use App\Http\Controllers\Api\FooterLinkGroupController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\LoyaltyPointController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\ImageUploadController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\VisitorStatsController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\ProductImageController;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/homepage', [HomepageSectionController::class, 'public']);

Route::get('/sliders', [SliderController::class, 'index']);
Route::get('/navigations', [NavigationController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/branches/cities',  [BranchController::class, 'cities']);
Route::get('/branches/nearest', [BranchController::class, 'nearest']);
Route::get('/branches', [BranchController::class, 'index']);
Route::get('/homepage/top-products',    [TopProductsController::class, 'index']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/categories',  [ProductController::class, 'categories']);
Route::get('/products/categories/shop', [ProductController::class, 'shopCategories']); 
Route::get('/products/brands',      [ProductController::class, 'brands']);
Route::get('/products/collections', [ProductController::class, 'collections']);
Route::get('/products/{id}',   [ProductController::class, 'show']);
Route::get('/promotions', [PromotionController::class, 'index']);
Route::get('/blog',         [App\Http\Controllers\Api\BlogController::class, 'index']);
Route::get('/blog/{slug}',  [App\Http\Controllers\Api\BlogController::class, 'show']);
Route::get('/static-pages/{type}', [ContentController::class, 'showStatic']);
Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::get('/faqs', [FaqController::class, 'public']);
Route::get('/orders/public/{invoice_number}', [OrderController::class, 'showPublic']);

Route::prefix('visitor')->group(function () {
    Route::post('/ping', [VisitorStatsController::class, 'ping']);
    Route::patch('/time', [VisitorStatsController::class, 'updateTime']);
});

Route::get('/agents/status', function () {
    $onlineCount = \App\Models\UserOnlineStatus::where('is_online', true)
        ->where('last_ping_at', '>=', now()->subMinutes(2))
        ->count();

    return response()->json([
        'any_online'   => $onlineCount > 0,
        'online_count' => $onlineCount,
    ]);
});

Route::prefix('rajaongkir')->group(function () {
    Route::get('/search-destination', [RajaOngkirController::class, 'searchDestination']);
    Route::post('/shipping-cost',     [RajaOngkirController::class, 'shippingCost']);
});

// Public chat routes (guest)
Route::prefix('chat')->group(function () {
    Route::post('sessions',                                  [Chat\SessionController::class, 'store']);
    Route::post('sessions/order',                             [Chat\SessionController::class, 'storeFromOrder']);
    Route::get ('sessions/{uuid}/by-token',                  [Chat\SessionController::class, 'showByToken']);
    Route::get ('sessions/{session:uuid}/messages',          [Chat\MessageController::class, 'index']);
    Route::post('sessions/{session:uuid}/messages',          [Chat\MessageController::class, 'store']);
    Route::post('sessions/{session:uuid}/messages/read',     [Chat\MessageController::class, 'markRead']);
    Route::post('sessions/{session:uuid}/typing',            [Chat\TypingController::class, 'start']);
    Route::delete('sessions/{session:uuid}/typing',          [Chat\TypingController::class, 'stop']);
    Route::post('sessions/{session:uuid}/attachments',       [Chat\AttachmentController::class, 'store']);
    Route::post('sessions/{session:uuid}/ping',              [Chat\PresenceController::class, 'ping']);
    Route::post('sessions/{session:uuid}/leave',              [Chat\PresenceController::class, 'leave']);
    Route::patch('sessions/{session:uuid}/rate',              [Chat\SessionController::class, 'rate']);
});

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/promo-codes/popup', [PromoCodeController::class, 'popupCodes']);
Route::post('/promo-codes/validate', [PromoCodeController::class, 'validateCode']);
Route::post('/webhook/olsera', [OlseraWebhookController::class, 'handle']);
Route::get('settings', [SiteSettingController::class, 'index']);
Route::get('/footer-links/grouped', [FooterLinkController::class, 'grouped']);

// Loyalty check/preview/history — public (dipakai saat checkout guest)
Route::prefix('loyalty')->group(function () {
    Route::get('check', [LoyaltyPointController::class, 'check']);
    Route::get('preview', [LoyaltyPointController::class, 'preview']);
    Route::get('history', [LoyaltyPointController::class, 'history']);
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES — auth:sanctum + permission check
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Profile — milik sendiri, tidak butuh permission spesifik
    Route::get('profile',           [ProfileController::class, 'show']);
    Route::put('profile',           [ProfileController::class, 'update']);
    Route::post('profile/avatar',   [ProfileController::class, 'uploadAvatar']);
    Route::delete('profile/avatar', [ProfileController::class, 'deleteAvatar']);
    Route::put('profile/password',  [ProfileController::class, 'updatePassword']);

    // ── Users ──
    Route::get   ('/users',                       [UserController::class, 'index'])        ->middleware('can:users_view');
    Route::post  ('/users',                       [UserController::class, 'store'])         ->middleware('can:users_create');
    Route::get   ('/users/{user}',                [UserController::class, 'show'])          ->middleware('can:users_view');
    Route::put   ('/users/{user}',                [UserController::class, 'update'])        ->middleware('can:users_edit');
    Route::delete('/users/{user}',                [UserController::class, 'destroy'])       ->middleware('can:users_delete');
    Route::post  ('/users/{user}/reset-password', [UserController::class, 'resetPassword']) ->middleware('can:users_edit');
    Route::post  ('/users/{user}/suspend',        [UserController::class, 'suspend'])       ->middleware('can:users_edit');
    Route::post  ('/users/{user}/activate',       [UserController::class, 'activate'])      ->middleware('can:users_edit');

    // ── Sliders ──
    Route::post  ('/sliders',         [SliderController::class, 'store'])  ->middleware('can:sliders_create');
    Route::post  ('/sliders/reorder', [SliderController::class, 'reorder'])->middleware('can:sliders_edit');
    Route::post  ('/sliders/{id}',    [SliderController::class, 'update']) ->middleware('can:sliders_edit');
    Route::delete('/sliders/{id}',    [SliderController::class, 'destroy'])->middleware('can:sliders_delete');

    // ── Navigation ──
    Route::get   ('/admin/navigations',      [NavigationController::class, 'adminIndex'])->middleware('can:navigations_view');
    Route::post  ('/admin/navigations',      [NavigationController::class, 'store'])     ->middleware('can:navigations_create');
    Route::put   ('/admin/navigations/{id}', [NavigationController::class, 'update'])    ->middleware('can:navigations_edit');
    Route::delete('/admin/navigations/{id}', [NavigationController::class, 'destroy'])   ->middleware('can:navigations_delete');

    Route::get('roles-permissions', [RoleController::class, 'permissions'])->middleware('can:roles_view');

    // ── Products ──
    Route::post  ('/products/bulk-delete',   [ProductController::class, 'bulkDelete'])->middleware('can:products_delete');
    Route::post  ('/products/import',        [ProductController::class, 'import'])    ->middleware('can:import_run');
    Route::post  ('/products/import-olsera', [ProductController::class, 'importOlsera'])->middleware('can:import_run');
    Route::get   ('/products/import-olsera/status/{importId}', [ProductController::class, 'importOlseraStatus'])->middleware('can:import_view');
    Route::post  ('/products',       [ProductController::class, 'store'])  ->middleware('can:products_create');
    Route::put   ('/products/{id}',  [ProductController::class, 'update']) ->middleware('can:products_edit');
    Route::delete('/products/{id}',  [ProductController::class, 'destroy'])->middleware('can:products_delete');
    Route::post  ('/products/upload-image', [ProductImageController::class, 'upload'])->middleware('can:products_create');
    Route::post  ('/products/delete-image', [ProductImageController::class, 'delete'])->middleware('can:products_delete');

    // Variants
    Route::get   ('/products/{id}/variants',  [ProductController::class, 'variants'])    ->middleware('can:products_view');
    Route::post  ('/products/{id}/variants',  [ProductController::class, 'storeVariant'])->middleware('can:products_create');
    Route::put   ('/variants/{variantId}',    [ProductController::class, 'updateVariant'])->middleware('can:products_edit');
    Route::delete('/variants/{variantId}',    [ProductController::class, 'destroyVariant'])->middleware('can:products_delete');

    // Featured Products
    Route::get  ('/admin/featured-products',            [TopProductsController::class, 'adminList'])->middleware('can:products_view');
    Route::post ('/admin/featured-products',             [TopProductsController::class, 'store'])    ->middleware('can:products_edit');
    Route::patch('/admin/featured-products/reorder',     [TopProductsController::class, 'reorder'])  ->middleware('can:products_edit');

    // ── Branches ──
    Route::post('/branches/bulk-delete', [BranchController::class, 'bulkDelete'])->middleware('can:branches_bulk_delete');
    Route::post  ('/branches',           [BranchController::class, 'store'])  ->middleware('can:branches_create');
    Route::get   ('/branches/{branch}',  [BranchController::class, 'show'])   ->middleware('can:store_locator_view');
    Route::match(['put', 'patch'], '/branches/{branch}', [BranchController::class, 'update'])->middleware('can:branches_edit');
    Route::delete('/branches/{branch}',  [BranchController::class, 'destroy'])->middleware('can:branches_delete');

    // ── Orders ──
    Route::get   ('/orders/stats',         [OrderController::class, 'stats'])       ->middleware('can:orders_view');
    Route::get   ('/orders/pending-count', [OrderController::class, 'pendingCount']) ->middleware('can:orders_view');
    Route::post  ('/orders/manual',        [OrderController::class, 'storeManual'])  ->middleware('can:orders_create');
    Route::get   ('/orders',               [OrderController::class, 'index'])       ->middleware('can:orders_view');
    Route::get   ('/orders/{id}',          [OrderController::class, 'show'])        ->middleware('can:orders_view');
    Route::get   ('/orders/{id}/invoice',  [OrderController::class, 'invoice'])     ->middleware('can:orders_view');
    Route::patch ('/orders/{id}/status',   [OrderController::class, 'updateStatus'])->middleware('can:orders_update_status');
    Route::delete('/orders/{id}',          [OrderController::class, 'destroy'])     ->middleware('can:orders_delete');

    // Order delete request
    Route::post ('/orders/{id}/request-delete',        [OrderDeleteRequestController::class, 'store'])       ->middleware('can:orders_view');
    Route::get  ('/order-delete-requests/pending-count',[OrderDeleteRequestController::class, 'pendingCount'])->middleware('can:orders_delete');
    Route::get  ('/order-delete-request',               [OrderDeleteRequestController::class, 'index'])      ->middleware('can:orders_delete');
    Route::patch('/order-delete-request/{id}/review',   [OrderDeleteRequestController::class, 'review'])     ->middleware('can:orders_delete');

    // Order revisions
    Route::patch('/orders/{id}/revise',   [OrderRevisionController::class, 'revise'])        ->middleware('can:orders_revise');
    Route::get  ('/orders/{id}/revisions',[OrderRevisionController::class, 'history'])        ->middleware('can:orders_revise');
    Route::get  ('/orders/products/search', [OrderRevisionController::class, 'searchProducts']);

    // ── Roles ──
    Route::get   ('/roles',      [RoleController::class, 'index'])  ->middleware('can:roles_view');
    Route::get   ('/roles/{id}', [RoleController::class, 'show'])   ->middleware('can:roles_view');
    Route::post  ('/roles',      [RoleController::class, 'store'])  ->middleware('can:roles_create');
    Route::put   ('/roles/{id}', [RoleController::class, 'update']) ->middleware('can:roles_edit');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('can:roles_delete');

    // ── Promo Codes ──
    Route::get   ('/promo-codes',              [PromoCodeController::class, 'index'])  ->middleware('can:promo_codes_view');
    Route::post  ('/promo-codes',              [PromoCodeController::class, 'store'])  ->middleware('can:promo_codes_create');
    Route::put   ('/promo-codes/{promoCode}',  [PromoCodeController::class, 'update']) ->middleware('can:promo_codes_edit');
    Route::delete('/promo-codes/{promoCode}',  [PromoCodeController::class, 'destroy'])->middleware('can:promo_codes_delete');

    // ── Reports ──
    Route::middleware('can:reports_view')->group(function () {
        Route::get('/sales-report',   [SalesReportController::class, 'index']);
        Route::get('/product-report', [ProductReportController::class, 'index']);
    });
    Route::middleware('can:reports_export')->group(function () {
        Route::get('/sales-report/export-excel',   [SalesReportController::class, 'exportExcel']);
        Route::get('/product-report/export-excel', [ProductReportController::class, 'exportExcel']);
    });

    // ── Site settings — kurir (shipping-couriers footer + rajaongkir toggle) ──
    Route::middleware('can:settings_couriers_view')->group(function () {
        Route::get('/settings/shipping-couriers',   [SiteSettingController::class, 'getShippingCouriers']);
        Route::get('/settings/rajaongkir-couriers', [SiteSettingController::class, 'getActiveCouriers']);
    });
    Route::middleware('can:settings_couriers_edit')->group(function () {
        Route::put ('/settings/shipping-couriers',   [SiteSettingController::class, 'saveShippingCouriers']);
        Route::post('/settings/courier-logo',        [SiteSettingController::class, 'uploadCourierLogo']);
        Route::put ('/settings/rajaongkir-couriers',  [SiteSettingController::class, 'saveActiveCouriers']);
    });

    // ── Site settings — general ──
    Route::put   ('settings/{key}',       [SiteSettingController::class, 'update'])        ->middleware('can:settings_edit');
    Route::post  ('settings/logo',        [SiteSettingController::class, 'uploadLogo'])     ->middleware('can:settings_edit');
    Route::delete('settings/logo',        [SiteSettingController::class, 'deleteLogo'])     ->middleware('can:settings_edit');
    Route::post  ('settings/favicon',     [SiteSettingController::class, 'uploadFavicon'])  ->middleware('can:settings_edit');
    Route::delete('settings/favicon',     [SiteSettingController::class, 'deleteFavicon'])  ->middleware('can:settings_edit');
    Route::post  ('settings/logo-footer', [SiteSettingController::class, 'uploadLogoFooter'])->middleware('can:settings_edit');
    Route::delete('settings/logo-footer', [SiteSettingController::class, 'deleteLogoFooter'])->middleware('can:settings_edit');

    // ── Homepage Sections ──
    Route::get   ('/admin/homepage-sections',                  [HomepageSectionController::class, 'index']) ->middleware('can:homepage_sections_view');
    Route::post  ('/admin/homepage-sections',                  [HomepageSectionController::class, 'store']) ->middleware('can:homepage_sections_create');
    Route::post  ('/admin/homepage-sections/reorder',          [HomepageSectionController::class, 'reorder'])->middleware('can:homepage_sections_reorder');
    Route::get   ('/admin/homepage-sections/{section}',        [HomepageSectionController::class, 'show'])  ->middleware('can:homepage_sections_view');
    Route::put   ('/admin/homepage-sections/{section}',        [HomepageSectionController::class, 'update'])->middleware('can:homepage_sections_edit');
    Route::delete('/admin/homepage-sections/{section}',        [HomepageSectionController::class, 'destroy'])->middleware('can:homepage_sections_delete');
    Route::patch ('/admin/homepage-sections/{section}/toggle', [HomepageSectionController::class, 'toggle']) ->middleware('can:homepage_sections_edit');

    // ── Footer Links ──
    Route::get   ('/admin/footer-link-groups',                   [FooterLinkGroupController::class, 'index'])  ->middleware('can:footer_links_manage');
    Route::post  ('/admin/footer-link-groups',                   [FooterLinkGroupController::class, 'store'])  ->middleware('can:footer_links_manage');
    Route::put   ('/admin/footer-link-groups/{footerLinkGroup}', [FooterLinkGroupController::class, 'update']) ->middleware('can:footer_links_manage');
    Route::delete('/admin/footer-link-groups/{footerLinkGroup}', [FooterLinkGroupController::class, 'destroy'])->middleware('can:footer_links_manage');

    Route::post  ('/admin/footer-links',              [FooterLinkController::class, 'store'])  ->middleware('can:footer_links_manage');
    Route::put   ('/admin/footer-links/{footerLink}', [FooterLinkController::class, 'update']) ->middleware('can:footer_links_manage');
    Route::delete('/admin/footer-links/{footerLink}', [FooterLinkController::class, 'destroy'])->middleware('can:footer_links_manage');
});

// ── Live Chat Agent/Admin (perlu login) ──────────────────────────────────
Route::middleware('auth:sanctum')->prefix('chat')->group(function () {
    Route::get   ('sessions',                        [Chat\SessionController::class, 'index'])  ->middleware('can:chat_view');
    Route::get   ('sessions/{session:uuid}',          [Chat\SessionController::class, 'show'])   ->middleware('can:chat_view');
    Route::get   ('sessions/{session:uuid}/kpi',      [Chat\ChatKpiController::class, 'show'])   ->middleware('can:chat_view');
    Route::delete('sessions/{session:uuid}',          [Chat\SessionController::class, 'destroy'])->middleware('can:chat_admin');
    Route::patch ('sessions/{session:uuid}/close',    [Chat\SessionController::class, 'close'])  ->middleware('can:chat_close');
    Route::patch ('sessions/{session:uuid}/reopen',   [Chat\SessionController::class, 'reopen']) ->middleware('can:chat_reopen');
    Route::patch ('sessions/{session:uuid}/assign',   [Chat\SessionController::class, 'assign']) ->middleware('can:chat_manage');
    Route::patch ('sessions/{session:uuid}/transfer', [Chat\SessionController::class, 'transfer'])->middleware('can:chat_manage');
    Route::get   ('agents-list',                       [Admin\QueueController::class, 'agents'])  ->middleware('can:chat_manage');
    Route::patch ('sessions/{session:uuid}/take',      [Chat\SessionController::class, 'take'])   ->middleware('can:chat_view');
});

// ── Agent ────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'can:chat_view'])->prefix('agent')->group(function () {
    Route::post('status/online',  [Agent\AgentStatusController::class, 'goOnline']);
    Route::post('status/offline', [Agent\AgentStatusController::class, 'goOffline']);
});

// ── Chat Admin ───────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'can:chat_admin'])->prefix('chat-admin')->group(function () {
    Route::get ('queue',                [Admin\QueueController::class, 'index']);
    Route::get ('queue/stats',          [Admin\QueueController::class, 'stats']);
    Route::post('queue/{entry}/assign', [Admin\QueueController::class, 'manualAssign']);
    Route::get ('agents',               [Admin\QueueController::class, 'agents']);
});

// ── Promotions (admin) ──────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get   ('/promotions',              [PromotionController::class, 'adminIndex'])->middleware('can:promotions_view');
    Route::post  ('/promotions',              [PromotionController::class, 'store'])     ->middleware('can:promotions_create');
    Route::patch ('/promotions/reorder',      [PromotionController::class, 'reorder'])   ->middleware('can:promotions_edit');
    Route::get   ('/promotions/{promotion}',  [PromotionController::class, 'show'])      ->middleware('can:promotions_view');
    Route::post  ('/promotions/{promotion}',  [PromotionController::class, 'update'])    ->middleware('can:promotions_edit'); // POST + _method=PUT
    Route::delete('/promotions/{promotion}',  [PromotionController::class, 'destroy'])   ->middleware('can:promotions_delete');
});

// ── CMS Content ──────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get   ('/contents/static/{type}',       [ContentController::class, 'showStatic'])->middleware('can:contents_view');
    Route::get   ('/contents',                     [ContentController::class, 'index'])     ->middleware('can:contents_view');
    Route::post  ('/contents',                     [ContentController::class, 'store'])     ->middleware('can:contents_create');
    Route::get   ('/contents/{content}',           [ContentController::class, 'show'])      ->middleware('can:contents_view');
    Route::post  ('/contents/{content}',           [ContentController::class, 'update'])    ->middleware('can:contents_edit');
    Route::put   ('/contents/{content}',           [ContentController::class, 'update'])    ->middleware('can:contents_edit');
    Route::delete('/contents/{content}',           [ContentController::class, 'destroy'])   ->middleware('can:contents_delete');
    Route::patch ('/contents/{content}/publish',   [ContentController::class, 'publish'])   ->middleware('can:contents_publish');
    Route::post  ('/images/upload',                [ImageUploadController::class, 'store']) ->middleware('can:media_upload');
});

// ── Loyalty (admin actions — deduct saldo, stats, recent) ─────────────────
Route::middleware(['auth:sanctum', 'can:loyalty_manage'])->prefix('loyalty')->group(function () {
    Route::post('deduct', [LoyaltyPointController::class, 'deduct']);
    Route::get('stats',   [LoyaltyPointController::class, 'stats']);
    Route::get('recent',  [LoyaltyPointController::class, 'recent']);
});

// ── Announcements, FAQ, Visitor Stats, Complaints (admin) ────────────────
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get   ('/announcements',                [AnnouncementController::class, 'adminIndex'])->middleware('can:announcement_view');
    Route::post  ('/announcements',                [AnnouncementController::class, 'store'])     ->middleware('can:announcement_create');
    Route::put   ('/announcements/{announcement}', [AnnouncementController::class, 'update'])    ->middleware('can:announcement_edit');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])   ->middleware('can:announcement_delete');
    Route::post  ('/announcements/reorder',        [AnnouncementController::class, 'reorder'])   ->middleware('can:announcement_reorder');

    Route::get   ('/faqs/categories', [FaqController::class, 'categories'])->middleware('can:faq_view');
    Route::patch ('/faqs/reorder',    [FaqController::class, 'reorder'])   ->middleware('can:faq_edit');
    Route::get   ('/faqs',            [FaqController::class, 'index'])    ->middleware('can:faq_view');
    Route::post  ('/faqs',            [FaqController::class, 'store'])    ->middleware('can:faq_create');
    Route::get   ('/faqs/{faq}',      [FaqController::class, 'show'])     ->middleware('can:faq_view');
    Route::match (['put', 'patch'], '/faqs/{faq}', [FaqController::class, 'update'])->middleware('can:faq_edit');
    Route::delete('/faqs/{faq}',      [FaqController::class, 'destroy'])  ->middleware('can:faq_delete');

    Route::get('/visitor-stats', [VisitorStatsController::class, 'index'])->middleware('can:visitor_stats_view');

    Route::get  ('/complaints',                    [ComplaintController::class, 'index'])       ->middleware('can:complaints_view');
    Route::patch('/complaints/{complaint}/status', [ComplaintController::class, 'updateStatus']) ->middleware('can:complaints_manage');
});