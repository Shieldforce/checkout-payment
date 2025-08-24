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
    ) {}

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'price'       => $this->price,
            'price_2'     => $this->price_2,
            'price_3'     => $this->price_3,
            'description' => $this->description,
            'img'         => $this->img,
            'quantity'    => $this->quantity,
        ];
    }
}
