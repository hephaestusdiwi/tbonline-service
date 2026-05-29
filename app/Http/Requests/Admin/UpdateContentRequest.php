<?php

namespace App\Http\Requests\Admin;

use App\Models\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('content'));
    }

    public function rules(): array
    {
        $content   = $this->route('content');
        $isArticle = ($content->type ?? $this->input('type')) === Content::TYPE_ARTICLE;
        
        return [
            'title'   => ['sometimes', 'required', 'string', 'max:255'],
            'slug'    => [
                'sometimes', 'nullable', 'string', 'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('contents', 'slug')->ignore($content->id),
            ],
            'body'     => ['sometimes', 'required', 'string'],
            'excerpt'  => ['nullable', 'string', 'max:500'],

             // Article-only
            'thumbnail' => $isArticle
                ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']
                : ['prohibited'],
            'tags'      => $isArticle ? ['nullable', 'array'] : ['prohibited'],
            'tags.*'    => $isArticle ? ['string', 'max:50'] : [],

            'status' => ['sometimes', 'required', Rule::in([Content::STATUS_DRAFT, Content::STATUS_PUBLISHED])],

            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ];
    }
}