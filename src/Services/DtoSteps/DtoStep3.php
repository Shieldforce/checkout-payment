<?php

namespace Shieldforce\CheckoutPayment\Services\DtoSteps;

class DtoStep3
{
    public function __construct(
        public int     $zipcode,
        public string  $street,
        public string  $district,
        public string  $city,
        public string  $state,
        public ?string $number = null,
        public ?string $complement = null,
        public ?bool   $visible = true,
    ) {}

    public function toArray(): array
    {
        return [
            'zipcode'    => $this->zipcode,
            'street'     => $this->street,
            'district'   => $this->district,
            'city'       => $this->city,
            'state'      => $this->state,
            'number'     => $this->number,
            'complement' => $this->complement,
            'visible'    => $this->visible,
        ];
    }
}
