<?php

namespace Shieldforce\CheckoutPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Ramsey\Uuid\Uuid;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;

class CppCheckout extends Model
{
    use Notifiable;

    protected $table = 'cpp_checkouts';

    protected $fillable = [
        'uuid',
        'cpp_gateway_id',
        'referencable_id',
        'referencable_type',
        'methods',
        'method_checked',
        'total_price',
        'due_date',
        'status',
        'return_gateway',
        'startOnStep',
        'url',
        'text_button_submit',
        'color_button_submit',
    ];

    protected $guarded = [];

    protected $casts = [
        'methods' => 'array',
    ];

    protected $attributes = [
        'methods' => '',
    ];

    public function initializeMethods(): void
    {
        if (empty($this->attributes['methods'])) {
            $this->attributes['methods'] = json_encode([
                MethodPaymentEnum::credit_card->value,
                MethodPaymentEnum::debit_card->value,
                MethodPaymentEnum::pix->value,
                MethodPaymentEnum::billet->value,
            ]);
        }
    }

    // hook de inicialização
    protected static function booted()
    {
        static::creating(function (CppCheckout $checkout) {
            $checkout->initializeMethods();
        });

        static::created(function (CppCheckout $checkout) {
            $now = now()->format('YmdHis');

            $checkout->update([
                'uuid' => Uuid::uuid3(
                    Uuid::NAMESPACE_DNS,
                    (string) "$checkout->id-$now"
                )->toString(),
            ]);
        });
    }

    public function gateway()
    {
        return $this->hasOne(
            CppGateways::class,
            'id',
            'cpp_gateway_id'
        );
    }

    public function step1()
    {
        return $this->hasMany(
            CppCheckoutStep1::class,
            'cpp_checkout_id',
            'id',
        );
    }

    public function step2()
    {
        return $this->hasMany(
            CppCheckoutStep2::class,
            'cpp_checkout_id',
            'id',
        );
    }

    public function step3()
    {
        return $this->hasMany(
            CppCheckoutStep3::class,
            'cpp_checkout_id',
            'id',
        );
    }

    public function step4()
    {
        return $this->hasMany(
            CppCheckoutStep4::class,
            'cpp_checkout_id',
            'id',
        );
    }

    public function referencable()
    {
        return $this->morphTo();
    }

    /**
     * Erro da última tentativa de geração de pagamento (MP ou Sicoob) para o
     * método atualmente selecionado neste checkout, quando houver.
     */
    public function lastGenerationError(): ?array
    {
        $step4 = $this->step4()->first();

        if (! $step4) {
            return null;
        }

        $field = match ((int) $this->method_checked) {
            MethodPaymentEnum::pix->value => 'response_pix_data',
            MethodPaymentEnum::billet->value => 'response_billet_data',
            MethodPaymentEnum::credit_card->value, MethodPaymentEnum::debit_card->value => 'response_credit_card_data',
            default => null,
        };

        if (! $field) {
            return null;
        }

        $response = json_decode($step4->{$field} ?? 'null', true);

        if (isset($response['error'])) {
            return [
                'message' => $response['message'] ?? 'Erro desconhecido ao gerar o pagamento.',
                'code' => $response['code'] ?? null,
            ];
        }

        if (isset($response['mensagens'][0]['mensagem'])) {
            return [
                'message' => $response['mensagens'][0]['mensagem'],
                'code' => $response['mensagens'][0]['codigo'] ?? null,
            ];
        }

        if (empty($response)) {
            return [
                'message' => 'Nenhuma tentativa de geração encontrada para o método de pagamento selecionado.',
                'code' => null,
            ];
        }

        return null;
    }
}
