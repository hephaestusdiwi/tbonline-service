<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\Message;
use App\Services\Chat\MessageService;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(Request $request, ChatSession $session): JsonResponse
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx',
            ],
        ]);

        $file       = $request->file('file');
        $mimeType   = $file->getMimeType();
        $isImage    = str_starts_with($mimeType, 'image/');
        $folder     = $isImage ? 'chat/images' : 'chat/documents';
        $path       = $file->store($folder, 'public');

        $sender = auth('sanctum')->user();

        $message = app(MessageService::class)->create($session, $sender, [
            'content' => $isImage ? '[Gambar]' : '[Dokumen: ' . $file->getClientOriginalName() . ']',
            'type'    => $isImage ? 'image' : 'file',
        ]);

        // simpan attachment
        $message->attachments()->create([
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_size'     => $file->getSize(),
            'mime_type'     => $mimeType,
            'url'           => Storage::url($path),
        ]);

        $message->load('sender', 'attachments');

        return response()->json(['data' => new \App\Http\Resources\MessageResource($message)], 201);
    }
}