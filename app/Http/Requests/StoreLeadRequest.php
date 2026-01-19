<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
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
            'form_name' => ['required', 'string', 'max:255'],
            'form_data' => ['required', 'array'],
            'submitted_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Check if the request body exceeds 1MB
        $content = $this->getContent();
        if (strlen($content) > 1024 * 1024) { // 1MB in bytes
            abort(413, 'Payload Too Large: Request body exceeds 1MB limit.');
        }
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'form_name.required' => 'The form name field is required.',
            'form_name.string' => 'The form name must be a string.',
            'form_name.max' => 'The form name may not be greater than 255 characters.',
            'form_data.required' => 'The form data field is required.',
            'form_data.array' => 'The form data must be a valid JSON object.',
            'submitted_at.date' => 'The submitted at field must be a valid date.',
        ];
    }
}
