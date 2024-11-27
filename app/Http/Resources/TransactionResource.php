<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use JsonSerializable;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            'transaction_id' => $this->transaction_id,
            'transaction_type' => $this->transaction_type,
            'debit' => (float)$this->debit,
            'credit' => (float)$this->credit,
            'user_info' => User::select(['phone', DB::raw("CONCAT(f_name, ' ' ,l_name) AS name")])->find($this->to_user_id),
            'sender' => User::select(['phone', DB::raw("CONCAT(f_name, ' ' ,l_name) AS name")])->find($this->from_user_id),
            'receiver' => User::select(['phone', DB::raw("CONCAT(f_name, ' ' ,l_name) AS name")])->find($this->to_user_id),
            'created_at' => $this->created_at,
            'amount' => (float)($this->debit + $this->credit),
        ];
    }
}
