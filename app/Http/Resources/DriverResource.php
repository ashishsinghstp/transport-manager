<?php

namespace App\Http\Resources;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Driver */
class DriverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'pay_rate' => $this->pay_rate,
            'company_address' => $this->company_address,
            'hst_number' => $this->hst_number,
            'status' => $this->status,
            'documents_count' => $this->whenCounted('documents'),
            'current_document' => $this->whenLoaded(
                'currentDocument',
                fn (): ?DocumentResource => $this->currentDocument
                    ? DocumentResource::make($this->currentDocument)
                    : null,
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
