<?php

namespace App\Http\Requests;

use App\Models\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Content::class);
    }

    public function rules(): array
    {
        $isArticle = $this->input('type') === Content::TYPE_ARTICLE;

        return [
            'type' => [
                'required',
                Rule::in([
                    Content::TYPE_ARTICLE,
                    content::TYPE_TOS,
                    Content::TYPE_SHIPPING_INFO,
                    Content::TYPE_RETURN_POLICY,
                ]),
            ],

            'title'    => ['required', 'string', 'max:255'],
            'slug'     => ['nullable', 'string', 'max:255', 'unique:contents,slug', 'regex:/^[a-z0-9-]+$/'],
            'body'     => ['required', 'string'],
            'excerpt'  => ['nullable', 'string', 'max:500'],
            'thumbnail' => $isArticle
                ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']
                : ['prohibited'],
            'tags'      => $isArticle ? ['nullable', 'array'] : ['prohibited'],
            'tags.*'    => $isArticle ? ['string', 'max:50'] : [],

            'status' => ['required', Rule::in([Content::STATUS_DRAFT, Content::STATUS_PUBLISHED])],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in'          => 'Tipe konten tidak valid.',
            'slug.unique'      => 'Slug sudah digunakan.',
            'slug.regex'       => 'Slug hanya boleh mengandung huruf kecil, angka, dan tanda hubung.',
            'thumbnail.image'  => 'File harus berupa gambar.',
            'thumbnail.max'    => 'Ukuran gambar maksimal 2MB.',
            'status.in'        => 'Status harus draft atau published.',
        ];
    }
}