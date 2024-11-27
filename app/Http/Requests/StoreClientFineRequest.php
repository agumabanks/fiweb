<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Client;

class StoreClientFineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Authorization logic: Ensure the user can add fines to the client
        $client = $this->route('client'); // Assuming route model binding
        return $this->user()->can('addFine', $client);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'note'   => 'nullable|string|max:500', // Optional note
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string>
     */
    public function messages()
    {
        return [
            'amount.required' => 'The fine amount is required.',
            'amount.numeric'  => 'The fine amount must be a number.',
            'amount.min'      => 'The fine amount must be at least 0.01 UGX.',
            'reason.required' => 'The reason for the fine is required.',
            'reason.max'      => 'The reason may not be greater than 255 characters.',
            'note.max'        => 'The note may not be greater than 500 characters.',
        ];
    }
}
