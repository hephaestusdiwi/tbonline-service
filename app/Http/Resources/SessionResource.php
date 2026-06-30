<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryAgent = $this->relationLoaded('agents')
            ? $this->agents->first(fn($a) => $a->pivot->role === 'primary' && $a->pivot->is_active)
            : null;

        return [
            'id'                => $this->id,
            'uuid'              => $this->uuid,
            'status'            => $this->status,
            'inquiry_type'      => $this->inquiry_type,
            'priority'          => $this->priority,
            'subject'           => $this->subject,
            'channel'           => $this->channel,
            'rating'            => $this->rating,
            'close_reason'      => $this->close_reason,
            'tags'              => $this->tags,
            'guest_name'        => $this->guest_name,
            'guest_phone'       => $this->guest_phone,
            'guest_email'       => $this->guest_email ?? null,
            'first_response_at' => $this->first_response_at,
            'resolved_at'       => $this->resolved_at,
            'closed_at'         => $this->closed_at,
            'created_at'        => $this->created_at,
            'visitor_left'      => (bool) $this->visitor_left,
            'rating' => $this->rating,

            'assigned_agent_name' => $primaryAgent?->name ?? null,

            'assigned_agent' => $primaryAgent ? [
                'id'     => $primaryAgent->id,
                'name'   => $primaryAgent->name,
                'avatar' => $primaryAgent->avatar
                                ? asset('storage/' . $primaryAgent->avatar)
                                : null,
            ] : null,

            'last_message'    => $this->last_message_content
                        ?? $this->whenLoaded('messages', fn() =>
                            $this->messages->last()?->content ?? null
                        ),
            'last_message_at' => $this->last_message_at
                        ?? $this->whenLoaded('messages', fn() =>
                            $this->messages->last()?->sent_at ?? null
                        ),

            'is_mine'           => $this->is_mine ?? false,
            'can_reply'         => $this->can_reply ?? false,

            'customer' => $this->whenLoaded('customer', fn() => [
                'id'    => $this->customer->id,
                'name'  => $this->customer->name,
                'email' => $this->customer->email,
            ]),
            'agents' => $this->whenLoaded('agents', fn() =>
                $this->agents->map(fn($agent) => [
                    'id'          => $agent->id,
                    'name'        => $agent->name,
                    'avatar'      => $agent->avatar
                                        ? asset('storage/' . $agent->avatar)
                                        : null,
                    'role'        => $agent->pivot->role,
                    'assigned_at' => $agent->pivot->assigned_at,
                ])
            ),
            'messages'    => MessageResource::collection($this->whenLoaded('messages')),
            'queue_entry' => $this->whenLoaded('queueEntry', fn() => [
                'position'               => $this->queueEntry->position,
                'estimated_wait_seconds' => $this->queueEntry->estimated_wait_seconds,
                'joined_at'              => $this->queueEntry->joined_at,
            ]),
        ];
    }
}