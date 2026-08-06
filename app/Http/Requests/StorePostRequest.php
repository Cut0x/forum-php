<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reply', $this->route('topic'));
    }

    public function rules(): array
    {
        $topic = $this->route('topic');

        return [
            'content' => ['required', 'string', 'min:1', 'max:20000'],
            'parent_id' => [
                'nullable',
                Rule::exists('posts', 'id')->where(
                    fn ($query) => $query->where('topic_id', $topic->id)->whereNull('deleted_at')
                ),
            ],
        ];
    }
}
