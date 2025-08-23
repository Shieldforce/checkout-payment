<?php

namespace Shieldforce\CheckoutPayment\Commands;

use Illuminate\Console\Command;

class CheckoutPaymentCommand extends Command
{
    public $signature = 'checkout-payment';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
