<?php

namespace Shieldforce\CheckoutPayment\Services;

use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Errors\CheckoutPaymentException;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Services\MercadoPago\MercadoPagoService;
use Shieldforce\CheckoutPayment\Services\Sicoob\Boleto\BoletoPixService;
use Throwable;

/**
 * Confirma ao vivo, direto no gateway (Mercado Pago/Sicoob), se um checkout já foi pago —
 * usado pra travar edição de valor/vencimento e exclusão sem depender só da flag local `paid`,
 * que pode estar desatualizada se ninguém confirmou o pagamento manualmente ainda.
 *
 * Sempre lança exceção quando não é possível confirmar (API fora do ar, gateway sem
 * configuração, etc.) em vez de assumir "não pago" nesse caso — quem chama decide bloquear.
 */
class PaymentVerificationService
{
    public function checkoutHasConfirmedPayment(CppCheckout $checkout): bool
    {
        $gatewayName = $checkout->gateway?->name;

        if ($gatewayName === TypeGatewayEnum::mercado_pago->value) {
            return $this->mercadoPagoConfirmed($checkout);
        }

        if ($gatewayName === TypeGatewayEnum::sicoob->value) {
            return $this->sicoobConfirmed($checkout);
        }

        // Sem gateway reconhecido vinculado, não há pagamento pra confirmar.
        return false;
    }

    /**
     * @param  iterable<CppCheckout>  $checkouts
     */
    public function anyConfirmedPayment(iterable $checkouts): bool
    {
        $confirmed = false;

        foreach ($checkouts as $checkout) {
            if ($this->checkoutHasConfirmedPayment($checkout)) {
                $confirmed = true;
            }
        }

        return $confirmed;
    }

    private function mercadoPagoConfirmed(CppCheckout $checkout): bool
    {
        if (! $checkout->uuid) {
            return false;
        }

        try {
            return (new MercadoPagoService)->pagamentoAprovado($checkout->uuid);
        } catch (Throwable $e) {
            throw new CheckoutPaymentException(
                'Não foi possível confirmar o pagamento no Mercado Pago: ' . $e->getMessage()
            );
        }
    }

    private function sicoobConfirmed(CppCheckout $checkout): bool
    {
        $step4 = $checkout->step4?->first();

        if (! $step4) {
            // Nenhum boleto/pix foi gerado ainda pra esse checkout, não pode estar pago.
            return false;
        }

        try {
            $consulta = (new BoletoPixService)->consult($checkout);
        } catch (Throwable $e) {
            throw new CheckoutPaymentException(
                'Não foi possível confirmar o pagamento no Sicoob: ' . $e->getMessage()
            );
        }

        $status = $consulta['resultado']['situacaoBoleto'] ?? null;

        if ($status === null) {
            throw new CheckoutPaymentException('Resposta inesperada do Sicoob ao verificar pagamento.');
        }

        // 'Baixado' pode ser cancelamento, não necessariamente pagamento confirmado.
        return $status === 'Liquidado';
    }
}
