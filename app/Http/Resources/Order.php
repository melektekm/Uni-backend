<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\OrderMenuItem as OrderMenuItemModel;
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
        $orderMenuItem = OrderMenuItemModel::where('order_id', $this->id);

        return [
            'id' => $this->id,
            'total_price' => $this->total_price,
            'coupon_code' => $this->coupon_code,
            'menu_items' => $orderMenuItem ? OrderMenuItem::collection($orderMenuItem->get()) : [],
            'created_at' => $this->getCreatedAtForHumansAttribute(),
        ];
    }
}
