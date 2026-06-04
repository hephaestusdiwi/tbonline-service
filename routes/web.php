<?php

use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [App\Http\Controllers\Api\SitemapController::class, 'index']);

Route::get('/{any}', function () {
    return view('home');
})->where('any', '.*');