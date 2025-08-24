<?php

namespace Shieldforce\CheckoutPayment\Services;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
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
                "cpp_gateway_id"    => $cppGateway->id,
                "referencable_id"   => $this->model->id,
                "referencable_type" => $this->model::class,
                "methods"           => json_encode($this->requiredMethods),
            ], []);
        }

        return $this;
    }

    public function step1(
        array $items
    )
    {
        $fieldsRequired = [
            "name",
            "price",
            "price_2",
            "price_3",
            "description",
            "img",
            "quantity",
        ];

        $validated = validator(
            ['items' => $items],
            [
                'items'         => 'required|array',
                'items.*.name'  => 'required|string',
                'items.*.email' => 'required|email',
            ]
        )->validate();

        $this->cppCheckout->step1()->updateOrCreate([
            "cpp_checkout_id" => $this->cppCheckout->id,
        ], [
            'items'   => json_encode($items),
            'visible' => true,
        ]);

        // Step 2 -> Dados do cliente ---
        /*$nameExplode = explode(" ", trim($model->order->client->name));
        $firstName   = $nameExplode[0];
        $lastName    = $nameExplode[1] ?? "-";
        $contact     = $model->order->client->contacts()->first();
        $prefix      = $contact->prefix ?? null;
        $number      = $contact->number ?? null;
        $phoneNumber = $prefix . $number;
        $cppCheckout->step2()->updateOrCreate([
            "cpp_checkout_id" => $cppCheckout->id,
        ], [
            "first_name"   => $firstName,
            "last_name"    => $lastName,
            "email"        => $model->order->client->email,
            "phone_number" => $phoneNumber,
            "document"     => $model->order->client->document,
            'visible'      => true,
        ]);*/

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
    }


}
