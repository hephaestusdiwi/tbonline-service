<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class AuditPermissions extends Command
{
    protected $signature = 'permissions:audit {--missing-only}';
    protected $description = 'List semua route API dan cek mana yang belum dilindungi permission middleware';

    public function handle()
    {
        $rows = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (!str_starts_with($uri, 'api/')) continue;

            $middlewares = $route->gatherMiddleware();
            $permissionMw = collect($middlewares)->first(fn($m) => str_starts_with($m, 'permission:'));
            $hasGuard = $permissionMw !== null;

            if ($this->option('missing-only') && $hasGuard) continue;

            $rows[] = [
                implode('|', $route->methods()),
                $uri,
                is_string($route->getActionName()) ? $route->getActionName() : 'Closure',
                $hasGuard ? str_replace('permission:', '', $permissionMw) : '❌ TIDAK ADA',
            ];
        }

        $this->table(['Method', 'URI', 'Controller@Action', 'Permission'], $rows);
        $this->info(count($rows) . ' route ditemukan.');
    }
}