<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'session_id'  => $this->session_id,
            'content'     => $this->content,
            'type'        => $this->type,
            'status'      => $this->status,
            'is_bot'      => $this->is_bot,
            'sender_type' => $this->sender_type,
            'sender_name' => $this->resolveSenderName(),
            'sent_at'     => $this->sent_at?->toISOString(),
            'is_read'     => $this->status === 'read',
            'attachment_url' => $this->attachments?->first()?->url ?? null,
            'sender' => $this->whenLoaded('sender', fn() => [
                'id'     => $this->sender->id,
                'name'   => $this->sender->name,
                'avatar' => $this->sender->avatar
                                ? asset('storage/' . $this->sender->avatar)
                                : null,
            ]),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }

    private function resolveSenderName(): string
    {
        return match ($this->sender_type) {
            'agent'  => $this->sender?->name ?? 'Agent',
            'bot'    => 'Two Brothers Bot',
            'system' => 'System',
            default  => 'Customer',
        };
    }
}