<?php

namespace Shieldforce\CheckoutPayment\Services\DtoSteps;

class DtoStep1
{
    public function __construct(
        public string $name,
        public float  $price,
        public float  $price_2,
        public float  $price_3,
        public string $description,
        public string $img,
        public string $quantity,
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
