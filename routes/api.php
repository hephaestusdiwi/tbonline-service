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

Route::post('/login', [AuthController::class, 'login']);
Route::get('/homepage', [HomepageSectionController::class, 'public']);

// Public routes
Route::get('/sliders', [SliderController::class, 'index']);
Route::get('/navigations', [NavigationController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/branches/cities',  [BranchController::class, 'cities']);
Route::get('/branches/nearest', [BranchController::class, 'nearest']);
Route::get('/branches', [BranchController::class, 'index']);
Route::get('/homepage/top-products',    [TopProductsController::class, 'index']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/categories',  [ProductController::class, 'categories']);
Route::get('/products/brands',      [ProductController::class, 'brands']);     
Route::get('/products/collections', [ProductController::class, 'collections']);
Route::get('/products/{id}',   [ProductController::class, 'show']);
Route::get('/promotions', [PromotionController::class, 'index']);
Route::get('/blog',         [App\Http\Controllers\Api\BlogController::class, 'index']);
Route::get('/blog/{slug}',  [App\Http\Controllers\Api\BlogController::class, 'show']);
Route::get('/static-pages/{type}', [ContentController::class, 'showStatic']);
Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::get('/faqs', [FaqController::class, 'public']);
// Taruh di LUAR group middleware auth
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
    Route::get ('sessions/{uuid}/by-token',                  [Chat\SessionController::class, 'showByToken']);
    Route::get ('sessions/{session:uuid}/messages',          [Chat\MessageController::class, 'index']);
    Route::post('sessions/{session:uuid}/messages',          [Chat\MessageController::class, 'store']);
    Route::post('sessions/{session:uuid}/messages/read',     [Chat\MessageController::class, 'markRead']);
    Route::post('sessions/{session:uuid}/typing',            [Chat\TypingController::class, 'start']);
    Route::delete('sessions/{session:uuid}/typing',          [Chat\TypingController::class, 'stop']);
    Route::post('sessions/{session:uuid}/attachments',       [Chat\AttachmentController::class, 'store']);
    Route::post('sessions/{session:uuid}/ping',              [Chat\PresenceController::class, 'ping']);
    Route::post('sessions/{session:uuid}/leave',             [Chat\PresenceController::class, 'leave']);
    Route::patch('sessions/{session:uuid}/rate',             [Chat\SessionController::class, 'rate']); 
});

Route::post('/orders', [OrderController::class, 'store']);
Route::post('/promo-codes/validate', [PromoCodeController::class, 'validateCode']);
Route::post('/webhook/olsera', [OlseraWebhookController::class, 'handle']);
Route::get('settings', [SiteSettingController::class, 'index']);
Route::get('/footer-links/grouped', [FooterLinkController::class, 'grouped']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Users
    Route::get   ('/users',                             [UserController::class, 'index'])        ->middleware('can:users_view');
    Route::post  ('/users',                             [UserController::class, 'store'])         ->middleware('can:users_create');
    Route::get   ('/users/{user}',                      [UserController::class, 'show'])          ->middleware('can:users_view');
    Route::put   ('/users/{user}',                      [UserController::class, 'update'])        ->middleware('can:users_edit');
    Route::delete('/users/{user}',                      [UserController::class, 'destroy'])       ->middleware('can:users_delete');
    Route::post  ('/users/{user}/reset-password',       [UserController::class, 'resetPassword']) ->middleware('can:users_edit');
    Route::post  ('/users/{user}/suspend',              [UserController::class, 'suspend'])       ->middleware('can:users_edit');
    Route::post  ('/users/{user}/activate',             [UserController::class, 'activate'])      ->middleware('can:users_edit');

    // Sliders
    Route::post('/sliders',         [SliderController::class, 'store'])->middleware('can:settings_edit');
    Route::post('/sliders/reorder', [SliderController::class, 'reorder'])->middleware('can:settings_edit');
    Route::post('/sliders/{id}',    [SliderController::class, 'update'])->middleware('can:settings_edit');
    Route::delete('/sliders/{id}',  [SliderController::class, 'destroy'])->middleware('can:settings_edit');

    // Navigation
    Route::get('/admin/navigations',         [NavigationController::class, 'adminIndex'])->middleware('can:settings_view');
    Route::post('/admin/navigations',        [NavigationController::class, 'store'])->middleware('can:settings_edit');
    Route::put('/admin/navigations/{id}',    [NavigationController::class, 'update'])->middleware('can:settings_edit');
    Route::delete('/admin/navigations/{id}', [NavigationController::class, 'destroy'])->middleware('can:settings_edit');
    Route::get('roles-permissions', [RoleController::class, 'permissions'])->middleware('can:roles_view');

    // Products
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete'])->middleware('can:products_delete');
    Route::post('/products/import',      [ProductController::class, 'import'])->middleware('can:products_create');
    Route::post('/products/import-olsera', [ProductController::class, 'importOlsera'])->middleware('can:products_create');
    Route::get('/products/import-olsera/status/{importId}', [ProductController::class, 'importOlseraStatus'])->middleware('can:products_create');
    Route::post('/products',             [ProductController::class, 'store'])->middleware('can:products_create');
    Route::put('/products/{id}',         [ProductController::class, 'update'])->middleware('can:products_edit');
    Route::delete('/products/{id}',      [ProductController::class, 'destroy'])->middleware('can:products_delete');

    // Variants - tambah ini
    Route::get('/products/{id}/variants',  [ProductController::class, 'variants'])->middleware('can:products_view');
    Route::post('/products/{id}/variants', [ProductController::class, 'storeVariant'])->middleware('can:products_create');
    Route::put('/variants/{variantId}',    [ProductController::class, 'updateVariant'])->middleware('can:products_edit');
    Route::delete('/variants/{variantId}', [ProductController::class, 'destroyVariant'])->middleware('can:products_delete');

    // Featured Products
    Route::get('/admin/featured-products',              [TopProductsController::class, 'adminList'])->middleware('can:products_view');
    Route::post('/admin/featured-products',             [TopProductsController::class, 'store'])->middleware('can:products_edit');
    Route::patch('/admin/featured-products/reorder',   [TopProductsController::class, 'reorder'])->middleware('can:products_edit');

    // Branches
    Route::post('/branches/bulk-delete', [BranchController::class, 'bulkDelete'])->middleware('can:settings_edit');
    Route::apiResource('branches', BranchController::class)->except(['index'])->middleware('can:settings_edit');

    // Orders
    Route::get('/orders/stats',         [OrderController::class, 'stats'])->middleware('can:orders_view');
    Route::get('/orders/pending-count', [OrderController::class, 'pendingCount'])->middleware('can:orders_view');
    Route::get('/orders',               [OrderController::class, 'index'])->middleware('can:orders_view');
    Route::get('/orders/{id}',          [OrderController::class, 'show'])->middleware('can:orders_view');
    Route::get('/orders/{id}/invoice',  [OrderController::class, 'invoice'])->middleware('can:orders_view');
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->middleware('can:orders_update_status');
    Route::delete('/orders/{id}',       [OrderController::class, 'destroy'])->middleware('can:orders_delete');

    // Order delete request
    Route::post('/orders/{id}/request-delete',             [OrderDeleteRequestController::class, 'store'])->middleware('can:orders_view');
    Route::get('/order-delete-requests/pending-count',     [OrderDeleteRequestController::class, 'pendingCount'])->middleware('can:orders_delete');
    Route::get('/order-delete-request',                    [OrderDeleteRequestController::class, 'index'])->middleware('can:orders_delete');
    Route::patch('/order-delete-request/{id}/review',      [OrderDeleteRequestController::class, 'review'])->middleware('can:orders_delete');

    // Order revisions
    Route::patch('/orders/{id}/revise',   [OrderRevisionController::class, 'revise'])->middleware('can:orders_revise');
    Route::get('/orders/{id}/revisions',  [OrderRevisionController::class, 'history'])->middleware('can:orders_revise');
    Route::get('/orders/products/search', [OrderRevisionController::class, 'searchProducts'])->middleware('can:orders_revise');

    // Roles
    Route::get('/roles',         [RoleController::class, 'index'])->middleware('can:roles_view');
    Route::get('/roles/{id}',    [RoleController::class, 'show'])->middleware('can:roles_view');
    Route::post('/roles',        [RoleController::class, 'store'])->middleware('can:roles_create');
    Route::put('/roles/{id}',    [RoleController::class, 'update'])->middleware('can:roles_edit');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('can:roles_delete');

    // Promo Codes
    Route::get('/promo-codes',                [PromoCodeController::class, 'index'])->middleware('can:settings_view');
    Route::post('/promo-codes',               [PromoCodeController::class, 'store'])->middleware('can:settings_edit');
    Route::put('/promo-codes/{promoCode}',    [PromoCodeController::class, 'update'])->middleware('can:settings_edit');
    Route::delete('/promo-codes/{promoCode}', [PromoCodeController::class, 'destroy'])->middleware('can:settings_edit');

    // Reports
    Route::get('/sales-report',              [SalesReportController::class, 'index']);
    Route::get('/sales-report/export-excel', [SalesReportController::class, 'exportExcel']);
    Route::get('/product-report',            [ProductReportController::class, 'index']);
    Route::get('/product-report/export-excel', [ProductReportController::class, 'exportExcel']);

    // Site settings
    Route::get('/settings/shipping-couriers',  [SiteSettingController::class, 'getShippingCouriers']);
    Route::put('/settings/shipping-couriers',  [SiteSettingController::class, 'saveShippingCouriers']);
    Route::post('/settings/courier-logo', [SiteSettingController::class, 'uploadCourierLogo']);
    Route::put('settings/{key}',      [SiteSettingController::class, 'update'])->middleware('can:settings_edit');
    Route::post('settings/logo',      [SiteSettingController::class, 'uploadLogo'])->middleware('can:settings_edit');
    Route::delete('settings/logo',    [SiteSettingController::class, 'deleteLogo'])->middleware('can:settings_edit');
    Route::post('settings/favicon',   [SiteSettingController::class, 'uploadFavicon'])->middleware('can:settings_edit');
    Route::delete('settings/favicon', [SiteSettingController::class, 'deleteFavicon'])->middleware('can:settings_edit');
    Route::post('settings/logo-footer',  [SiteSettingController::class, 'uploadLogoFooter'])->middleware('can:settings_edit');
    Route::delete('settings/logo-footer',[SiteSettingController::class, 'deleteLogoFooter'])->middleware('can:settings_edit');

    // Profile
    Route::get('profile',           [ProfileController::class, 'show']);
    Route::put('profile',           [ProfileController::class, 'update']);
    Route::post('profile/avatar',   [ProfileController::class, 'uploadAvatar']);
    Route::delete('profile/avatar', [ProfileController::class, 'deleteAvatar']);
    Route::put('profile/password',  [ProfileController::class, 'updatePassword']);

    Route::get('/admin/homepage-sections',              [HomepageSectionController::class, 'index'])->middleware('can:settings_view');
    Route::post('/admin/homepage-sections',             [HomepageSectionController::class, 'store'])->middleware('can:settings_edit');
    Route::post('/admin/homepage-sections/reorder',     [HomepageSectionController::class, 'reorder'])->middleware('can:settings_edit');
    Route::get('/admin/homepage-sections/{section}',    [HomepageSectionController::class, 'show'])->middleware('can:settings_view');
    Route::put('/admin/homepage-sections/{section}',    [HomepageSectionController::class, 'update'])->middleware('can:settings_edit');
    Route::delete('/admin/homepage-sections/{section}', [HomepageSectionController::class, 'destroy'])->middleware('can:settings_edit');
    Route::patch('/admin/homepage-sections/{section}/toggle', [HomepageSectionController::class, 'toggle'])->middleware('can:settings_edit');

    Route::get   ('/admin/footer-link-groups',                    [FooterLinkGroupController::class, 'index'])->middleware('can:settings_view');
    Route::post  ('/admin/footer-link-groups',                    [FooterLinkGroupController::class, 'store'])->middleware('can:settings_edit');
    Route::put   ('/admin/footer-link-groups/{footerLinkGroup}',  [FooterLinkGroupController::class, 'update'])->middleware('can:settings_edit');
    Route::delete('/admin/footer-link-groups/{footerLinkGroup}',  [FooterLinkGroupController::class, 'destroy'])->middleware('can:settings_edit');

    Route::post  ('/admin/footer-links',              [FooterLinkController::class, 'store'])->middleware('can:settings_edit');
    Route::put   ('/admin/footer-links/{footerLink}', [FooterLinkController::class, 'update'])->middleware('can:settings_edit');
    Route::delete('/admin/footer-links/{footerLink}', [FooterLinkController::class, 'destroy'])->middleware('can:settings_edit');
 
});

// ── Live Chat Agent/Admin (perlu login) ──────────────────────────────────
Route::middleware('auth:sanctum')->prefix('chat')->group(function () {
    Route::get('sessions',                             [Chat\SessionController::class, 'index'])->middleware('can:chat_view');
    Route::get('sessions/{session:uuid}',              [Chat\SessionController::class, 'show'])->middleware('can:chat_view');
    Route::delete('sessions/{session:uuid}',           [Chat\SessionController::class, 'destroy'])->middleware('can:chat_admin');
    Route::patch('sessions/{session:uuid}/close',      [Chat\SessionController::class, 'close'])->middleware('can:chat_close');
    Route::patch('sessions/{session:uuid}/reopen',     [Chat\SessionController::class, 'reopen'])->middleware('can:chat_reopen');
    Route::patch('sessions/{session:uuid}/assign',     [Chat\SessionController::class, 'assign'])->middleware('can:chat_manage');
    Route::patch('sessions/{session:uuid}/transfer',   [Chat\SessionController::class, 'transfer'])->middleware('can:chat_manage');
    Route::get('agents-list',                          [Admin\QueueController::class, 'agents'])->middleware('can:chat_manage');
    Route::patch('sessions/{session:uuid}/take',       [Chat\SessionController::class, 'take'])->middleware('can:chat_view');
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
    Route::get ('agents',              [Admin\QueueController::class, 'agents']);
    // Route::get ('reports/overview',    [Admin\ReportController::class, 'overview']);
    // Route::get ('reports/agents',      [Admin\ReportController::class, 'agents']);
    // Route::get ('reports/sessions',    [Admin\ReportController::class, 'reports']);
});

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('/promotions',                  [PromotionController::class, 'adminIndex']);
    Route::post('/promotions',                 [PromotionController::class, 'store']);
    Route::patch('/promotions/reorder',        [PromotionController::class, 'reorder']);
    Route::get('/promotions/{promotion}',      [PromotionController::class, 'show']);
    Route::post('/promotions/{promotion}',     [PromotionController::class, 'update']);   // POST + _method=PUT
    Route::delete('/promotions/{promotion}',   [PromotionController::class, 'destroy']);
});

// ── CMS Content ──────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('/contents/static/{type}',       [ContentController::class, 'showStatic']);
    Route::get('/contents',                     [ContentController::class, 'index']);
    Route::post('/contents',                    [ContentController::class, 'store']);
    Route::get('/contents/{content}',           [ContentController::class, 'show']);
    Route::post('/contents/{content}',          [ContentController::class, 'update']);
    Route::put('/contents/{content}',           [ContentController::class, 'update']);
    Route::delete('/contents/{content}',        [ContentController::class, 'destroy']);
    Route::patch('/contents/{content}/publish', [ContentController::class, 'publish']);
    Route::post('/images/upload',               [ImageUploadController::class, 'store']);
});

Route::prefix('loyalty')->group(function () {
    Route::get('check', [LoyaltyPointController::class, 'check']);
    Route::get('preview', [LoyaltyPointController::class, 'preview']);
    Route::get('history', [LoyaltyPointController::class, 'history']);
 
});

Route::middleware(['auth:sanctum'])->prefix('loyalty')->group(function () {
    Route::post('deduct', [LoyaltyPointController::class, 'deduct']);
    Route::get('stats',   [LoyaltyPointController::class, 'stats']);
    Route::get('recent',  [LoyaltyPointController::class, 'recent']);
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get   ('/announcements',                    [AnnouncementController::class, 'adminIndex'])->middleware('can:settings_view');
    Route::post  ('/announcements',                    [AnnouncementController::class, 'store'])->middleware('can:settings_edit');
    Route::put   ('/announcements/{announcement}',     [AnnouncementController::class, 'update'])->middleware('can:settings_edit');
    Route::delete('/announcements/{announcement}',     [AnnouncementController::class, 'destroy'])->middleware('can:settings_edit');
    Route::post  ('/announcements/reorder',            [AnnouncementController::class, 'reorder'])->middleware('can:settings_edit');

    Route::get('/faqs/categories', [FaqController::class, 'categories']);
    Route::patch('/faqs/reorder',  [FaqController::class, 'reorder']);
 
    Route::apiResource('/faqs', FaqController::class)->except(['create', 'edit']);
    Route::get('/visitor-stats', [VisitorStatsController::class, 'index']);
    Route::get  ('/complaints',                     [ComplaintController::class, 'index']);
    Route::patch('/complaints/{complaint}/status',  [ComplaintController::class,  'updateStatus']);
});