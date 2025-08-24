<?php

namespace Shieldforce\CheckoutPayment\Services\DtoSteps;

class DtoStep4
{
    public function __construct(
        public ?int    $card_number,
        public ?string $card_validate,
        public ?string $card_payer_name,
        public ?string $base_qrcode,
        public ?string $url_qrcode,
        public ?string $url_billet,
        public ?bool   $visible,
        public ?bool   $checked = null,
    ) {}

    public function toArray(): array
    {
        return [
            'card_number'     => $this->card_number,
            'card_validate'   => $this->card_validate,
            'card_payer_name' => $this->card_payer_name,
            'base_qrcode'     => $this->base_qrcode,
            'url_qrcode'      => $this->url_qrcode,
            'url_billet'      => $this->url_billet,
            'visible'         => $this->visible,
            'checked'         => $this->checked,
        ];
    }
}
