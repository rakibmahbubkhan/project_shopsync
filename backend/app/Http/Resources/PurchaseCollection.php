<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => PurchaseResource::collection($this->collection),
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'total' => $this->total(),
            ]
        ];
    }
}

