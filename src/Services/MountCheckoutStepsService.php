<?php

namespace Shieldforce\CheckoutPayment\Services;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Models\CppGateways;
use Shieldforce\CheckoutPayment\Rules\CpfCnpjRule;
use Shieldforce\CheckoutPayment\Rules\ImageUrlRule;
use Shieldforce\CheckoutPayment\Services\DtoSteps\DtoStep2;
use Shieldforce\CheckoutPayment\Services\DtoSteps\DtoStep3;
use Shieldforce\CheckoutPayment\Services\DtoSteps\DtoStep4;

class MountCheckoutStepsService
{
    protected CppCheckout $cppCheckout;

    public function __construct(
        protected Model $model,
        protected array $requiredMethods = [
            MethodPaymentEnum::credit_card->value,
            MethodPaymentEnum::pix->value,
            MethodPaymentEnum::billet->value,
        ]
    ) {}

    public function handle()
    {
        // Gateway Ativo ---
        $cppGateway = CppGateways::where('active', true)->first();
        if (isset($cppGateway->id)) {

            $this->cppCheckout = CppCheckout::updateOrCreate([
                'cpp_gateway_id'    => $cppGateway->id,
                'referencable_id'   => $this->model->id,
                'referencable_type' => $this->model::class,
                'methods'           => json_encode($this->requiredMethods),
            ], []);
        }

        return $this;
    }

    public function step1(
        array $items,
        bool  $visible = true,
    )
    {
        $validator = Validator::make(
            ['items' => $items],
            [
                'items.*.name'        => [
                    'required',
                    'string',
                ],
                'items.*.price'       => [
                    'required',
                    'numeric',
                    'regex:/^\d+(\.\d{1,2})?$/',
                ],
                'items.*.price_2'     => [
                    'nullable',
                    'numeric',
                    'regex:/^\d+(\.\d{1,2})?$/',
                ],
                'items.*.price_3'     => [
                    'nullable',
                    'numeric',
                    'regex:/^\d+(\.\d{1,2})?$/',
                ],
                'items.*.description' => [
                    'nullable',
                    'string',
                ],
                'items.*.img'         => [
                    'nullable',
                    'url',
                    new ImageUrlRule,
                ],
                'items.*.quantity'    => [
                    'required',
                    'integer',
                ],
            ]
        );

        if ($validator->fails()) {
            $errorsHtml = '<ul>';
            foreach ($validator->errors()->all() as $error) {
                $errorsHtml .= "<li><strong>{$error}</strong></li>";
            }
            $errorsHtml .= '</ul>';

            Notification::make('errors')
                ->persistent()
                ->danger()
                ->title('Erro ao gerar checkout!')
                ->body($errorsHtml)
                ->send();

            return $this;
        }

        $this->cppCheckout->step1()->updateOrCreate([
            'cpp_checkout_id' => $this->cppCheckout->id,
        ], [
            'items'   => json_encode($items),
            'visible' => $visible,
        ]);

        return $this;
    }

    public function step2(DtoStep2 $data)
    {
        $data = $data->toArray();

        $required = [
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'email'        => $data['email'],
            'phone_number' => $data['phone_number'],
            'document'     => $data['document'],
            'visible'      => $data['visible'],
        ];

        $validator = Validator::make(
            $required,
            [
                'first_name'   => [
                    'required',
                    'string',
                    'min:3',
                    'max:50',
                ],
                'last_name'    => [
                    'required',
                    'string',
                    'min:3',
                    'max:100',
                ],
                'email'        => [
                    'required',
                    'email',
                    'max:100',
                ],
                'phone_number' => [
                    'required',
                    'numeric',
                    'min:8',
                    'max:13',
                ],
                'document'     => [
                    'required',
                    'numeric',
                    new CpfCnpjRule(),
                ],
                'visible'      => [
                    'nullable',
                    'boolean',
                ],
            ]
        );

        if ($validator->fails()) {
            $errorsHtml = '<ul>';
            foreach ($validator->errors()->all() as $error) {
                $errorsHtml .= "<li><strong>{$error}</strong></li>";
            }
            $errorsHtml .= '</ul>';

            Notification::make('errors')
                ->persistent()
                ->danger()
                ->title('Erro ao gerar checkout!')
                ->body($errorsHtml)
                ->send();

            return $this;
        }

        $this->cppCheckout->step2()->updateOrCreate([
            'cpp_checkout_id' => $this->cppCheckout->id,
        ], $required);

        return $this;
    }

    public function step3(DtoStep3 $data)
    {
        $data = $data->toArray();

        $required = [
            'zipcode'    => $data['zipcode'],
            'street'     => $data['street'],
            'district'   => $data['district'],
            'city'       => $data['city'],
            'state'      => $data['state'],
            'number'     => $data['number'],
            'complement' => $data['complement'],
            'visible'    => $data['visible'],
        ];

        $validator = Validator::make(
            $required,
            [
                'zipcode'    => [
                    'required',
                    'numeric',
                ],
                'street'     => [
                    'required',
                    'string',
                ],
                'district'   => [
                    'required',
                    'string',
                ],
                'city'       => [
                    'required',
                    'string',
                ],
                'state'      => [
                    'required',
                    'string',
                ],
                'number'     => [
                    'nullable',
                    'string',
                ],
                'complement' => [
                    'nullable',
                    'string',
                ],
                'visible'    => [
                    'nullable',
                    'boolean',
                ],
            ]
        );

        if ($validator->fails()) {
            $errorsHtml = '<ul>';
            foreach ($validator->errors()->all() as $error) {
                $errorsHtml .= "<li><strong>{$error}</strong></li>";
            }
            $errorsHtml .= '</ul>';

            Notification::make('errors')
                ->persistent()
                ->danger()
                ->title('Erro ao gerar checkout!')
                ->body($errorsHtml)
                ->send();

            return $this;
        }

        $this->cppCheckout->step3()->updateOrCreate([
            'cpp_checkout_id' => $this->cppCheckout->id,
        ], $required);

        return $this;
    }

    public function step4(DtoStep4 $data)
    {
        $data = $data->toArray();

        $required = [
            'card_number'     => $data['card_number'],
            'card_validate'   => $data['card_validate'],
            'card_payer_name' => $data['card_payer_name'],
            'base_qrcode'     => $data['base_qrcode'],
            'url_qrcode'      => $data['url_qrcode'],
            'url_billet'      => $data['url_billet'],
            'visible'         => $data['visible'],
        ];

        $validator = Validator::make(
            $required,
            [
                'card_number'     => [
                    'nullable',
                    'numeric',
                ],
                'card_validate'   => [
                    'nullable',
                    'string',
                ],
                'card_payer_name' => [
                    'nullable',
                    'string',
                ],
                'base_qrcode'     => [
                    'nullable',
                    'string',
                ],
                'url_qrcode'      => [
                    'nullable',
                    'url',
                ],
                'url_billet'      => [
                    'nullable',
                    'url',
                ],
                'visible'         => [
                    'nullable',
                    'boolean',
                ],
            ]
        );

        if ($validator->fails()) {
            $errorsHtml = '<ul>';
            foreach ($validator->errors()->all() as $error) {
                $errorsHtml .= "<li><strong>{$error}</strong></li>";
            }
            $errorsHtml .= '</ul>';

            Notification::make('errors')
                ->persistent()
                ->danger()
                ->title('Erro ao gerar checkout!')
                ->body($errorsHtml)
                ->send();

            return $this;
        }

        $this->cppCheckout->step4()->updateOrCreate([
            'cpp_checkout_id' => $this->cppCheckout->id,
        ], $required);

        return $this;
    }
}
