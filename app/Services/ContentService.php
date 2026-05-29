<?php

namespace App\Services;

use App\Models\Content;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ContentService
{
    public function list(array $filters): LengthAwarePaginator
    {
        $query = Content::with(['author', 'updater'])
            ->when(
                isset($filters['type']),
                fn ($q) => $q->where('type', $filters['type'])
            )
            ->when(
                isset($filters['status']),
                fn ($q) => $q->where('status', $filters['status'])
            )
            ->when(
                ! empty($filters['search']),
                fn ($q) => $q->search($filters['search'])
            )
            ->when(
                ! empty($filters['tag']),
                fn ($q) => $q->whereJsonContains('tags', $filters['tag'])
            )
            ->latest('updated_at');
 
        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data, int $authorId): Content
    {
        $data['author_id']  = $authorId;
        $data['updated_by'] = $authorId;
 
        if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
            $data['thumbnail'] = $this->uploadThumbnail($data['thumbnail']);
        }
 
        if ($data['status'] === Content::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
 
        return Content::create($data);
    }

    public function update(Content $content, array $data, int $updaterId): Content
    {
        $data['updated_by'] = $updaterId;
 
        if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
            // Hapus thumbnail lama
            if ($content->thumbnail) {
                Storage::disk('public')->delete($content->thumbnail);
            }
            $data['thumbnail'] = $this->uploadThumbnail($data['thumbnail']);
        }
 
        // Sudah published tapi sekarang draft → hapus published_at
        if (isset($data['status'])) {
            if ($data['status'] === Content::STATUS_PUBLISHED && ! $content->published_at) {
                $data['published_at'] = now();
            } elseif ($data['status'] === Content::STATUS_DRAFT) {
                $data['published_at'] = null;
            }
        }
 
        $content->update($data);
 
        return $content->fresh(['author', 'updater']);
    }

    public function togglePublish(Content $content, int $updaterId): Content
    {
        $newStatus = $content->isPublished()
            ? Content::STATUS_DRAFT
            : Content::STATUS_PUBLISHED;

        $content->update([
            'status'       => $newStatus,
            'updated_by'   => $updaterId,
            'published_at' => $newStatus === Content::STATUS_PUBLISHED ? now(): null,
        ]);

        return $content->fresh();
    }

    public function delete(Content $content): void
    {
        $content->delete();
    }

    public function forceDelete(Content $content): void
    {
        if ($content->thumbnail) {
            Storage::disk('public')->delete($content->thumbnail);
        }
        $content->forceDelete();
    }

    private function uploadThumbnail(UploadedFile $file): string
    {
        return $file->store('thumbnails', 'public');
    }
}