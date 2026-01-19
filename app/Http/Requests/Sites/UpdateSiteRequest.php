<?php

namespace App\Http\Requests\Sites;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('domain')) {
            $this->merge([
                'domain' => $this->extractDomain($this->input('domain')),
            ]);
        }
    }

    /**
     * Extract bare domain from a URL or domain string.
     */
    private function extractDomain(string $value): string
    {
        $value = trim($value);

        // Remove protocol (http:// or https://)
        $value = preg_replace('#^https?://#i', '', $value);

        // Remove www. prefix
        $value = preg_replace('#^www\.#i', '', $value);

        // Remove trailing slash and any path
        $value = preg_replace('#/.*$#', '', $value);

        return strtolower($value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['sometimes', 'required', 'string', 'max:255'],
            'domain' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i',
                Rule::unique('sites', 'domain')->ignore($this->route('site')),
            ],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'domain.regex' => 'The domain must be a valid domain format (e.g., example.com).',
            'domain.unique' => 'This domain has already been registered.',
        ];
    }
}
