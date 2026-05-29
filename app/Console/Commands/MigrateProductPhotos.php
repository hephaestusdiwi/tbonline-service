<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigrateProductPhotos extends Command
{
    protected $signature = 'products:migrate-photos
                            {--dry-run : Tampilkan yang akan diproses tanpa benar-benar download}
                            {--id=     : Proses satu produk saja berdasarkan ID}
                            {--chunk=50: Jumlah produk per batch}';

    protected $description = 'Download foto produk dari URL eksternal, convert ke WebP, simpan ke local storage';

    public function handle(ProductImageService $imageService): int
    {
        $query = Product::where(function ($q) {
            $q->where('photo_1', 'like', 'http%')
              ->orWhere('photo_2', 'like', 'http%')
              ->orWhere('photo_3', 'like', 'http%')
              ->orWhere('photo_4', 'like', 'http%')
              ->orWhere('photo_5', 'like', 'http%')
              ->orWhere('photo_6', 'like', 'http%')
              ->orWhere('photo_7', 'like', 'http%')
              ->orWhere('photo_8', 'like', 'http%')
              ->orWhere('photo_9', 'like', 'http%')
              ->orWhere('photo_10', 'like', 'http%');
        });

        if ($this->option('id')) {
            $query->where('id', (int) $this->option('id'));
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('✓ Semua foto produk sudah local. Tidak ada yang perlu dimigrasi.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$total} produk dengan foto eksternal.");
        $this->newLine();

        if ($this->option('dry-run')) {
            $query->each(function (Product $product) {
                $this->line("  [{$product->id}] {$product->name}");
                for ($i = 1; $i <= 10; $i++) {
                    $field = "photo_{$i}";
                    if (! empty($product->$field) && str_starts_with($product->$field, 'http')) {
                        $this->line("        {$field}: {$product->$field}");
                    }
                }
            });
            return self::SUCCESS;
        }

        $success   = 0;
        $failed    = 0;
        $chunkSize = (int) $this->option('chunk');

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(" %current%/%max% [%bar%] %percent:3s%% — %message%");
        $bar->setMessage('Memulai...');
        $bar->start();

        $query->chunkById($chunkSize, function ($products) use ($imageService, &$success, &$failed, $bar) {
            foreach ($products as $product) {
                $bar->setMessage(Str::limit($product->name, 40));

                $subfolder = 'olsera/' . Str::slug($product->name);
                $updated   = false;

                for ($i = 1; $i <= 10; $i++) {
                    $field = "photo_{$i}";

                    if (empty($product->$field) || ! str_starts_with($product->$field, 'http')) {
                        continue;
                    }

                    $localPath = $imageService->downloadAndConvert($product->$field, $subfolder);

                    if ($localPath) {
                        $product->$field = $localPath;
                        $updated         = true;
                    } else {
                        $failed++;
                        $this->newLine();
                        $this->warn("  ✗ Gagal: [{$product->id}] {$product->name} → {$field}");
                    }
                }

                if ($updated) {
                    $product->saveQuietly(); // skip model events
                    $success++;
                }

                $bar->advance();
            }
        });

        $bar->setMessage('Selesai!');
        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Status', 'Jumlah'],
            [
                ['✓ Berhasil', $success],
                ['✗ Gagal',    $failed],
                ['Total',      $success + $failed],
            ]
        );

        if ($failed > 0) {
            $this->warn("Ada {$failed} foto yang gagal didownload. Cek storage/logs/laravel.log untuk detail.");
            $this->line("Jalankan ulang command ini — hanya foto yang masih URL eksternal yang akan diproses ulang.");
        }

        return self::SUCCESS;
    }
}