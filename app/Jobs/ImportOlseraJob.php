<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportOlseraJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(
        public string $importId,
        public string $mode,
        public array $rows,
    ) {}

    public function handle(ProductImageService $imageService): void
    {
        $groups = $this->groupRows($this->rows);
        $total  = count($groups);

        $imported = 0; $updated = 0; $skipped = 0; $failed = 0; $errors = [];

        if ($this->mode === 'replace') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Product::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $processed = 0;

        foreach ($groups as $key => $group) {
            $meta     = $group['meta'];
            $variants = $group['variants'];
            $name     = trim($meta['name']);
            $processed++;

            try {
                DB::beginTransaction();

                $productData = $this->mapOlseraRow($meta);

                if (empty($variants)) {
                    $productData['stock_qty'] = (int) ($meta['stock_qty'] ?? 0);
                }
                
                $subfolder   = 'olsera/' . Str::slug($name);
                $productData = array_merge(
                    $productData,
                    $imageService->downloadProductPhotos(
                        $this->extractPhotoFields($productData),
                        $subfolder,
                    ),
                );

                if ($this->mode === 'skip') {
                    $exists = Product::whereRaw('LOWER(name) = ?', [$key])->exists();
                    if ($exists) {
                        $skipped++;
                        DB::rollBack();
                        $this->updateProgress($processed, $total, $imported, $updated, $skipped, $failed, $errors);
                        continue;
                    }
                    $product = Product::create($productData);
                    $this->createVariantsFromOlsera($product, $variants);
                    $imported++;

                } elseif ($this->mode === 'replace') {
                    $product = Product::create($productData);
                    $this->createVariantsFromOlsera($product, $variants);
                    $imported++;

                } elseif ($this->mode === 'update') {
                    $product = Product::whereRaw('LOWER(name) = ?', [$key])->first();
                    if ($product) {
                        $product->update($productData);
                        $product->optionTypes()->delete();
                        $product->variants()->delete();
                        $this->createVariantsFromOlsera($product, $variants);
                        $updated++;
                    } else {
                        $product = Product::create($productData);
                        $this->createVariantsFromOlsera($product, $variants);
                        $imported++;
                    }
                }

                DB::commit();

            } catch (\Throwable $e) {
                DB::rollBack();
                $errors[] = "Produk '$name': " . $e->getMessage();
                $failed++;
            }

            if ($processed % 10 === 0 || $processed === $total) {
                $this->updateProgress($processed, $total, $imported, $updated, $skipped, $failed, $errors);
            }
        }

        $this->updateProgress($processed, $total, $imported, $updated, $skipped, $failed, $errors, true);
    }

    // ─────────────────────────────────────────────
    // Helpers (dipindah dari ProductController)
    // ─────────────────────────────────────────────

    private function groupRows(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $name = trim($row['name'] ?? '');
            if ($name === '') continue;

            $key = strtolower($name);
            if (! isset($groups[$key])) {
                $groups[$key] = ['meta' => $row, 'variants' => []];
            }

            $variantName = trim($row['variant_names'] ?? '');
            if ($variantName !== '') {
                $groups[$key]['variants'][] = [
                    'label'           => $variantName,
                    'variant_label'   => trim($row['variant_label'] ?? ''),
                    'sku'             => $row['sku'] ?? null,
                    'barcode'         => $row['barcode'] ?? null,
                    'sell_price'      => is_numeric($row['sell_price'] ?? null) ? (float) $row['sell_price'] : null,
                    'buy_price'       => is_numeric($row['buy_price'] ?? null) ? (float) $row['buy_price'] : null,
                    'market_price'    => is_numeric($row['market_price'] ?? null) ? (float) $row['market_price'] : null,
                    'pos_sell_price'  => is_numeric($row['pos_sell_price'] ?? null) ? (float) $row['pos_sell_price'] : null,
                    'stock_qty'       => (int) ($row['stock_qty'] ?? 0),
                    'hold_qty'        => (int) ($row['hold_qty'] ?? 0),
                    'low_stock_alert' => (int) ($row['low_stock_alert'] ?? 2),
                    'qty_fast_moving' => (int) ($row['qty_fast_moving'] ?? 0),
                    'weight_kg'       => is_numeric($row['weight_kg'] ?? null) ? (float) $row['weight_kg'] : null,
                ];
            }
        }
        return $groups;
    }

    private function createVariantsFromOlsera(Product $product, array $variants): void
    {
        if (empty($variants)) return;

        $optionTypeName = $variants[0]['variant_label'] ?: 'Varian';
        $optionType     = $product->optionTypes()->create(['name' => $optionTypeName, 'position' => 0]);

        foreach ($variants as $pos => $v) {
            $optionValue = $optionType->values()->create(['value' => $v['label'], 'position' => $pos]);

            $variant = $product->variants()->create([
                'label'           => $v['label'],
                'sku'             => $v['sku'] ?: null,
                'barcode'         => $v['barcode'] ?: null,
                'buy_price'       => $v['buy_price'],
                'sell_price'      => $v['sell_price'],
                'pos_sell_price'  => $v['pos_sell_price'],
                'market_price'    => $v['market_price'],
                'stock_qty'       => $v['stock_qty'],
                'hold_qty'        => $v['hold_qty'],
                'low_stock_alert' => $v['low_stock_alert'],
                'qty_fast_moving' => $v['qty_fast_moving'],
                'weight_kg'       => $v['weight_kg'],
                'photo'           => null,
                'is_active'       => 1,
                'position'        => $pos,
            ]);

            $variant->optionValues()->sync([$optionValue->id]);
        }
    }

    private function mapOlseraRow(array $row): array
    {
        $toBool = fn($val) => in_array(strtolower((string) $val), ['1', 'yes', 'true', 'y']) ? 1 : 0;

        return [
            'name'                   => trim($row['name'] ?? ''),
            'alternative_name'       => $row['alternative_name'] ?? null,
            'classification_id'      => $row['classification_id'] ?? null,
            'category'               => $row['category'] ?? null,
            'collections'            => $row['collections'] ?? null,
            'brand'                  => $row['brand'] ?? null,
            'condition_id'           => $row['condition_id'] ?? 'N',
            'sku'                    => $row['sku'] ?? null,
            'barcode'                => $row['barcode'] ?? null,
            'buy_price'              => is_numeric($row['buy_price'] ?? null) ? (float) $row['buy_price'] : null,
            'market_price'           => is_numeric($row['market_price'] ?? null) ? (float) $row['market_price'] : null,
            'sell_price'             => is_numeric($row['sell_price'] ?? null) ? (float) $row['sell_price'] : 0,
            'pos_sell_price'         => is_numeric($row['pos_sell_price'] ?? null) ? (float) $row['pos_sell_price'] : null,
            'pos_sell_price_dynamic' => (int) ($row['pos_sell_price_dynamic'] ?? 0),
            'comission'              => (float) ($row['comission'] ?? 0),
            'track_inventory'        => (int) ($row['track_inventory'] ?? 1),
            'uom'                    => $row['uom'] ?? null,
            'weight_kg'              => is_numeric($row['weight_kg'] ?? null) ? (float) $row['weight_kg'] : null,
            'loyalty_points'         => (int) ($row['loyalty_points'] ?? 0),
            'published'              => $toBool($row['published'] ?? 1),
            'pos_hidden'             => $toBool($row['pos_hidden'] ?? 0),
            'description'            => $row['description'] ?? null,
            'photo_1'                => $row['photo_1'] ?? null,
            'photo_2'                => $row['photo_2'] ?? null,
            'photo_3'                => $row['photo_3'] ?? null,
            'photo_4'                => $row['photo_4'] ?? null,
            'photo_5'                => $row['photo_5'] ?? null,
            'photo_6'                => $row['photo_6'] ?? null,
            'photo_7'                => $row['photo_7'] ?? null,
            'photo_8'                => $row['photo_8'] ?? null,
            'photo_9'                => $row['photo_9'] ?? null,
            'photo_10'               => $row['photo_10'] ?? null,
            'notes'                  => $row['notes'] ?? null,
            'tax_free_item'          => $row['tax_free_item'] ?? 'No',
        ];
    }

    private function extractPhotoFields(array $data): array
    {
        $fields = [];
        for ($i = 1; $i <= 10; $i++) {
            $key = "photo_{$i}";
            if (! empty($data[$key])) {
                $fields[$key] = $data[$key];
            }
        }
        return $fields;
    }

    private function updateProgress(int $processed, int $total, int $imported, int $updated, int $skipped, int $failed, array $errors, bool $done = false): void
    {
        Cache::put("olsera_import_{$this->importId}", [
            'status'    => $done ? 'completed' : 'processing',
            'processed' => $processed,
            'total'     => $total,
            'imported'  => $imported,
            'updated'   => $updated,
            'skipped'   => $skipped,
            'failed'    => $failed,
            'errors'    => array_slice($errors, -50),
            'percent'   => $total > 0 ? round($processed / $total * 100, 1) : 0,
        ], now()->addHours(2));
    }
}