<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentTransactionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Implement your authorization logic if needed
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date_range' => 'nullable|array|size:2',
            'date_range.start' => 'nullable|date',
            'date_range.end' => 'nullable|date|after_or_equal:date_range.start',
            'agent_id' => 'nullable|exists:users,id',
            'client_id' => 'nullable|exists:clients,id', // Adjust as per your clients table
            'status' => 'nullable|in:paid,pending,rejected', // Adjust as per your status values
            'branch_id' => 'nullable|exists:branches,id',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0|gte:min_amount',
        ];
    }
}
