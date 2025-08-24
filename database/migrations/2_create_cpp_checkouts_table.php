<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('cpp_checkouts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cpp_gateway_id');
            $table->foreign('cpp_gateway_id')
                ->references('id')
                ->on('cpp_gateways');

            $table->string('referencable_type')->nullable();
            $table->unsignedBigInteger('referencable_id')->nullable();
            $table->json('methods')->nullable();
            $table->uuid('uuid')->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->integer('status')
                ->default(
                    \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::criado->value
                );
            $table->json("return_gateway")->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpp_checkouts');
    }
};
