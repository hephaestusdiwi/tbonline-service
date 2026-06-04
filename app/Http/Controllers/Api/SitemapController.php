<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Content;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $products = Product::select('slug', 'updated_at')
            ->whereNotNull('slug')
            ->orderBy('updated_at', 'desc')
            ->get();

        $blogs = Content::select('slug', 'updated_at')
            ->where('type', 'blog')
            ->where('status', 'published')
            ->whereNotNull('slug')
            ->orderBy('updated_at', 'desc')
            ->get();

        $content = view('sitemap', compact('products', 'blogs'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}