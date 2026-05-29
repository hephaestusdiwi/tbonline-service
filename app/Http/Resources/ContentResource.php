<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'type'  => $this->type,
 
            'title'   => $this->title,
            'slug'    => $this->slug,
            'body'    => $this->body,
            'excerpt' => $this->excerpt,
 
            // Article-only (null untuk static pages)
            'thumbnail'     => $this->when($this->isArticle(), $this->thumbnail_url),
            'tags'          => $this->when($this->isArticle(), $this->tags ?? []),
 
            'status'       => $this->status,
            'published_at' => $this->published_at?->toISOString(),
 
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
 
            'author' => $this->whenLoaded('author', fn () => [
                'id'   => $this->author->id,
                'name' => $this->author->name,
            ]),
 
            'updated_by_user' => $this->whenLoaded('updater', fn () => $this->updater ? [
                'id'   => $this->updater->id,
                'name' => $this->updater->name,
            ] : null),
 
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
 
            // Permissions — dikirim ke FE supaya bisa hide/show button
            'can' => $this->when($request->user(), fn () => [
                'update'  => $request->user()->can('update', $this->resource),
                'publish' => $request->user()->can('publish', $this->resource),
                'delete'  => $request->user()->can('delete', $this->resource),
            ]),
        ];
    }
}