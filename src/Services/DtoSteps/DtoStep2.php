<?php

namespace Shieldforce\CheckoutPayment\Services\DtoSteps;

class DtoStep2
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $phone_number,
        public string $document,
        public ?bool  $visible = null,
    ) {}

    public function toArray(): array
    {
        return [
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'email'        => $this->email,
            'phone_number' => $this->phone_number,
            'document'     => $this->document,
            'visible'      => $this->visible,
        ];
    }
}
