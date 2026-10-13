<?php

namespace App\Http\Requests\Leads;

use App\Models\Service;
use App\Support\Turnstile;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

/**
 * Fields shared by the contact and consultation forms. Visitors give a
 * name and at least one way to reach them; no financial records or files.
 */
abstract class LeadRequest extends FormRequest
{
    public const CONSENT_TEXT = 'I agree that ALCO Associate Solutions may use these details to respond to my enquiry.';

    /** Hidden field that people never see; bots tend to fill it in. */
    public const HONEYPOT = 'website';

    public function authorize(): bool
    {
        return true;
    }

    public function isLikelySpam(): bool
    {
        return filled($this->input(self::HONEYPOT));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))) ?: null,
            'phone' => trim((string) $this->input('phone')) ?: null,
            'service_id' => $this->filled('service_id') ? $this->input('service_id') : null,
        ]);
    }

    /** @return array<string, array<int, ValidationRule|Closure|string|In>> */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'required_without:phone', 'email:rfc', 'max:160'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:40', 'regex:/^\+?[0-9 ()\-]{7,20}$/'],
            'service_id' => ['nullable', 'integer', Rule::in(Service::published()->pluck('id')->all())],
            'consent' => ['accepted'],
        ];

        if (Turnstile::enabled()) {
            $rules['cf-turnstile-response'] = ['required', 'string', function (string $attribute, mixed $value, Closure $fail): void {
                if (! Turnstile::verify(is_string($value) ? $value : null, $this->ip())) {
                    $fail('The spam check did not pass. Please try again.');
                }
            }];
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Please tell us your name.',
            'email.required_without' => 'Please give an email address or a phone number.',
            'phone.required_without' => 'Please give a phone number or an email address.',
            'email.email' => 'Please check the email address.',
            'phone.regex' => 'Please check the phone number. Use digits, spaces and an optional + only.',
            'service_id.in' => 'Please choose a service from the list.',
            'consent.accepted' => 'Please confirm we may use your details to respond.',
            'cf-turnstile-response.required' => 'Please complete the spam check.',
        ];
    }
}
