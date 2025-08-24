<?php

namespace Shieldforce\CheckoutPayment\Services\DtoSteps;

use Shieldforce\CheckoutPayment\Enums\TypePeopleEnum;

class DtoStep2
{
    public function __construct(
        public TypePeopleEnum $people_type,
        public string         $first_name,
        public string         $last_name,
        public string         $email,
        public int            $phone_number,
        public int            $document,
        public ?bool          $visible = null,
    ) {}

    public function toArray(): array
    {
        return [
            'people_type'  => $this->people_type,
            'first_name'   => $this->first_name,
            'last_name'    => $this->last_name,
            'email'        => $this->email,
            'phone_number' => $this->phone_number,
            'document'     => $this->document,
            'visible'      => $this->visible,
        ];
    }
}
