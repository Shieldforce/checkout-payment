<?php

namespace Shieldforce\CheckoutPayment\Services;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Models\CppGateways;

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
        array $items
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
                    Rule::imageFile(),
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

            return Notification::make('errors')
                ->persistent()
                ->danger()
                ->title('Erro ao gerar checkout!')
                ->body($errorsHtml)
                ->send();
        }

        $this->cppCheckout->step1()->updateOrCreate([
            'cpp_checkout_id' => $this->cppCheckout->id,
        ], [
            'items'   => json_encode($items),
            'visible' => true,
        ]);

        return $this;
    }

    public function step2(
        $first_name,
        $last_name,
        $email,
        $phone_number,
        $document,
        $visible = true,
    )
    {
        $required = [
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'email'        => $email,
            'phone_number' => $phone_number,
            'document'     => $document,
            'visible'      => $visible,
        ];

        $validator = Validator::make(
            $required,
            [
                'first_name'   => [
                    'required',
                    'string',
                ],
                'last_name'    => [
                    'required',
                    'string',
                ],
                'email'        => [
                    'required',
                    'email',
                ],
                'phone_number' => [
                    'required',
                    'string',
                ],
                'document'     => [
                    'required',
                    'string',
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

            return Notification::make('errors')
                ->persistent()
                ->danger()
                ->title('Erro ao gerar checkout!')
                ->body($errorsHtml)
                ->send();
        }

        $this->cppCheckout->step1()->updateOrCreate([
            'cpp_checkout_id' => $this->cppCheckout->id,
        ], [
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'email'        => $email,
            'phone_number' => $phone_number,
            'document'     => $document,
            'visible'      => $visible,
        ]);

        // Step 3 -> Endereço do cliente ---
        /*$address = $model->order->client->addresses()->where("main", 1)->first();
        $cppCheckout->step3()->updateOrCreate([
            "cpp_checkout_id" => $cppCheckout->id,
        ], [
            "zipcode"    => $address->zipcode,
            "street"     => $address->street,
            "district"   => $address->district,
            "city"       => $address->city,
            "state"      => $address->state,
            "number"     => $address->number,
            "complement" => $address->complement,
            'visible'    => true,
        ]);*/

        return $this;
    }
}
