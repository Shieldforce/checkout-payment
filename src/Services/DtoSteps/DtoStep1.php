<?php

namespace Shieldforce\CheckoutPayment\Services\DtoSteps;

class DtoStep1
{
    public function __construct(
        public string  $name,
        public float   $price,
        public string  $quantity,
        public ?float  $price_2 = null,
        public ?float  $price_3 = null,
        public ?string $description = null,
        public ?string $img = null,
    )
    {
        return [
            'name'        => $name,
            'price'       => $price,
            'price_2'     => $price_2,
            'price_3'     => $price_3,
            'description' => $description,
            'img'         => $img,
            'quantity'    => $quantity,
        ];
    }
}
