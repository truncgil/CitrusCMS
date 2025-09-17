<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool 
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
     return [
    'title' => 'required|string|max:255',
    'slug'  => 'required|string|max:255|unique:pages,slug',
    'content' => 'nullable|string',
    'excerpt' => 'nullable|string',
    'status'  => 'in:draft,published,archived',
    'template'=> 'nullable|string|max:100',
    'parent_id'=>'nullable|exists:pages,id',
    'is_homepage'=>'boolean',
  ];
    }
}
