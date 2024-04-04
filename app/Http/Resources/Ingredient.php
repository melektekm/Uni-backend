<?php

namespace App\Http\Resources;

use App\Models\Inventory;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Ingredient as IngredientModel;
use Illuminate\Support\Facades\Log;

class Order extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $ingredient = IngredientModel::where('id', $this->id);

        return [
            'id' => $this->id,
            'itemPrice' => $this->itemPrice,
        ];

    }
}