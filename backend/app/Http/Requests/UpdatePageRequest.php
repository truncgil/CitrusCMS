<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
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
      $id = $this->route('page');
  return [
    'title' => 'sometimes|required|string|max:255',
    'slug'  => "sometimes|required|string|max:255|unique:pages,slug,$id",
    'content' => 'nullable|string',
    'excerpt' => 'nullable|string',
    'status'  => 'in:draft,published,archived',
    'template'=> 'nullable|string|max:100',
    'parent_id'=>'nullable|exists:pages,id',
    'is_homepage'=>'boolean',
  ];
    }
}
