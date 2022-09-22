<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $status = 'PENDING';
        if($request->status == 1)
        {
            $status = 'APPROVED';
        }
        return [
            'amount'=> $this->amount,
            'term' => $this->period,
            'status'=>$status,
            'created_at'  => $this->created_at->format('d-m-Y')
        ];
    }
}
