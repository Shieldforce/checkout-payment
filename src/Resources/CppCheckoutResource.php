<?php

namespace Shieldforce\CheckoutPayment\Resources;

use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;
use Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum;
use Shieldforce\CheckoutPayment\Enums\StatusTransactionEnum;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Enums\TypeStepEnum;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep2;
use Shieldforce\CheckoutPayment\Resources\CppCheckoutResource\Pages\ListCppCheckouts;
use Shieldforce\CheckoutPayment\Services\MercadoPago\MercadoPagoService;
use Shieldforce\CheckoutPayment\Services\PaymentVerificationService;
use Shieldforce\CheckoutPayment\Services\Permissions\CanTrait;
use Shieldforce\CheckoutPayment\Services\Sicoob\Boleto\BoletoPixService;

class CppCheckoutResource extends Resource
{
    use CanTrait;

    protected static ?string $model = CppCheckout::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $label = 'Cobrança';

    protected static ?string $pluralLabel = 'Cobranças';

    protected static ?string $navigationLabel = 'Cobranças';

    protected static ?string $slug = 'checkouts-payment';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->orderBy('due_date', 'desc')
                    ->orderByRaw('
                        CASE status
                            WHEN ? THEN 1
                            WHEN ? THEN 2
                            WHEN ? THEN 3
                            ELSE 4
                        END
                    ', [
                        StatusCheckoutEnum::criado->value,
                        StatusCheckoutEnum::pendente->value,
                        StatusCheckoutEnum::finalizado->value,
                    ]);
            })
            ->columns([
                /*TextColumn::make('referencable_id')
                    ->label('TRI')
                    ->description("Id de ref."),
                TextColumn::make('referencable_type')
                    ->label('TRT')
                    ->formatStateUsing(function ($state) {
                        return str_replace(["\\","App","Models"], ["","",""], $state);
                    })
                    ->description("Tipo de ref."),*/

                TextColumn::make('step2')
                    ->label('Cliente')
                    ->formatStateUsing(function ($record) {
                        $first = $record->step2()->first();

                        return $first ? $first->first_name . ' ' . $first->last_name : '-';
                    }),

                TextColumn::make('referencable_id')
                    ->label('ID Fatura')
                    ->url(function (Model $record) {
                        if ($record->referencable_type !== Transaction::class) {
                            return null;
                        }

                        return TransactionResource::getUrl('edit', ['record' => $record->referencable_id]);
                    })
                    ->openUrlInNewTab(),

                TextColumn::make('methods')
                    ->label('Métodos/Pag')
                    ->description('Métodos liberados')
                    ->formatStateUsing(function ($state) {
                        $array = json_decode($state, true);
                        $tags = [];
                        foreach ($array as $key => $value) {
                            $tags[] = MethodPaymentEnum::from($value)->label();
                        }

                        return implode(', ', $tags);
                    })
                    ->html(),

                TextColumn::make('total_price')
                    ->label('Valor')
                    ->description('Valor da cobrança!')
                    ->formatStateUsing(function ($state) {
                        return 'R$ ' . number_format($state, 2, ',', '.');
                    }),

                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->formatStateUsing(function ($state) {
                        return Carbon::createFromFormat('Y-m-d', $state)
                            ->format('d/m/Y');
                    }),

                BadgeColumn::make('status')
                    ->formatStateUsing(function ($state, $record) {
                        $label = StatusCheckoutEnum::labelEnum($state);

                        return $record->payment_forced ? "{$label} (Pagamento Forçado)" : $label;
                    })
                    ->color(fn ($state, $record) => $record->payment_forced ? 'warning' : StatusCheckoutEnum::colorEnum($state))
                    ->icon(fn ($state, $record) => $record->payment_forced ? 'heroicon-o-shield-exclamation' : null)
                    ->label('Status')
                    ->sortable(),

                BadgeColumn::make('startOnStep')
                    ->formatStateUsing(fn ($state, $record) => TypeStepEnum::from($state)->label())
                    ->color('success')
                    ->label('Passo Atual')
                    ->sortable(),

            ])
            ->filters([

                SelectFilter::make('document')
                    ->label('CPF/CNPJ')
                    ->searchable()
                    ->optionsLimit(5)
                    ->options(
                        CppCheckoutStep2::query()
                            ->whereNotNull('document')
                            ->select('document')
                            ->distinct()
                            ->orderBy('document')
                            ->pluck('document', 'document')
                            ->toArray()
                    )
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('step2', function ($subQuery) use ($data) {
                                $subQuery->where('document', $data['value']);
                            });
                        }
                    }),

                SelectFilter::make('email')
                    ->label('Email')
                    ->searchable()
                    ->optionsLimit(5)
                    ->options(
                        CppCheckoutStep2::query()
                            ->whereNotNull('email')
                            ->select('email')
                            ->distinct()
                            ->orderBy('email')
                            ->pluck('email', 'email')
                            ->toArray()
                    )
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('step2', function ($subQuery) use ($data) {
                                $subQuery->where('email', $data['value']);
                            });
                        }
                    }),

                SelectFilter::make('first_name')
                    ->label('Primeiro Nome')
                    ->searchable()
                    ->optionsLimit(5)
                    ->options(
                        CppCheckoutStep2::query()
                            ->whereNotNull('first_name')
                            ->select('first_name')
                            ->distinct()
                            ->orderBy('first_name')
                            ->pluck('first_name', 'first_name')
                            ->toArray()
                    )
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('step2', function ($subQuery) use ($data) {
                                $subQuery->where('first_name', $data['value']);
                            });
                        }
                    }),

                Filter::make('vencimento')
                    ->columnSpan(2)
                    ->form([
                        DatePicker::make('due_date_start')
                            ->label('Vencimento (Inicial)'),
                        DatePicker::make('due_date_end')
                            ->label('Vencimento (Final)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_date_start'],
                                function (Builder $query, $date): Builder {
                                    return $query->whereDate('due_date', '>=', $date);
                                }
                            )
                            ->when(
                                $data['due_date_end'],
                                function (Builder $query, $date): Builder {
                                    return $query->whereDate('due_date', '<=', $date);
                                }
                            );
                    })->columns(2),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(StatusCheckoutEnum::options())
                    ->query(
                        fn (Builder $query, array $data) => filled($data['value'])
                            ? $query->where('status', $data['value'])
                            : $query
                    ),

            ], Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->headerActions([

            ])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    // Tables\Actions\EditAction::make(),

                    Tables\Actions\DeleteAction::make()
                        ->visible(
                            fn ($record) => Auth::user()?->hasAnyRoles('Administrator')
                                && $record->status == StatusCheckoutEnum::criado->value
                        )
                        ->before(function (Model $record) {
                            try {
                                $paid = (new PaymentVerificationService)->checkoutHasConfirmedPayment($record);
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Não foi possível confirmar o pagamento agora')
                                    ->body('Não deu pra consultar o gateway pra confirmar se essa cobrança já foi paga. Tente novamente em instantes.')
                                    ->danger()
                                    ->persistent()
                                    ->send();

                                throw new Halt;
                            }

                            if ($paid) {
                                Notification::make()
                                    ->title('Essa cobrança já foi paga')
                                    ->body('Cobranças já pagas (confirmadas no gateway) não podem ser excluídas.')
                                    ->danger()
                                    ->persistent()
                                    ->send();

                                throw new Halt;
                            }
                        }),

                    Tables\Actions\Action::make('informar_pagamento')
                        ->label(fn (Model $record) => self::informarPagamentoLabel($record))
                        ->icon('heroicon-o-banknotes')
                        ->color(fn (Model $record) => $record->payment_forced ? 'warning' : 'success')
                        ->modalHeading(fn (Model $record) => self::informarPagamentoLabel($record))
                        ->modalDescription(
                            'Marca essa cobrança (e a fatura vinculada, quando houver) como paga manualmente. '
                            . 'Nenhuma consulta automática ao gateway vai reverter esse status depois.'
                        )
                        ->modalSubmitActionLabel('Salvar')
                        ->form(fn (Model $record) => [
                            RichEditor::make('payment_forced_description')
                                ->label('Descrição')
                                ->helperText('Explique como/quando o pagamento foi confirmado (obrigatório).')
                                ->required()
                                ->default($record->payment_forced_description),

                            FileUpload::make('receipts')
                                ->label('Comprovante(s)')
                                ->helperText('PDF ou imagem. Pelo menos um arquivo é obrigatório.')
                                ->multiple()
                                ->acceptedFileTypes([
                                    'application/pdf',
                                    'image/png',
                                    'image/jpeg',
                                    'image/jpg',
                                    'image/webp',
                                ])
                                ->directory("checkout-payment/forced-payment-receipts/{$record->id}")
                                ->default($record->forcedPaymentReceipts()->pluck('path')->toArray())
                                ->required(),
                        ])
                        ->action(function (array $data, Model $record) {
                            $user = Auth::user();
                            $disk = Storage::disk(config('filament.default_filesystem_disk', 'public'));
                            $keptPaths = $data['receipts'] ?? [];

                            DB::transaction(function () use ($record, $data, $user, $disk, $keptPaths) {
                                $record->update([
                                    'status' => StatusCheckoutEnum::finalizado->value,
                                    'startOnStep' => TypeStepEnum::finalizado->value,
                                    'payment_forced' => true,
                                    'payment_forced_at' => now(),
                                    'payment_forced_by_user_id' => $user?->id,
                                    'payment_forced_by_user_name' => $user?->name,
                                    'payment_forced_description' => $data['payment_forced_description'],
                                ]);

                                $record->forcedPaymentReceipts()
                                    ->whereNotIn('path', $keptPaths)
                                    ->get()
                                    ->each(function ($receipt) use ($disk) {
                                        $disk->delete($receipt->path);
                                        $receipt->delete();
                                    });

                                $existingPaths = $record->forcedPaymentReceipts()->pluck('path')->toArray();

                                foreach ($keptPaths as $path) {
                                    if (in_array($path, $existingPaths, true)) {
                                        continue;
                                    }

                                    $record->forcedPaymentReceipts()->create([
                                        'path' => $path,
                                        'origin_name' => basename($path),
                                        'extension' => pathinfo($path, PATHINFO_EXTENSION),
                                        'mime' => $disk->exists($path) ? $disk->mimeType($path) : null,
                                        'size' => $disk->exists($path) ? $disk->size($path) : null,
                                    ]);
                                }

                                if ($record->referencable_type === Transaction::class && $record->referencable) {
                                    $record->referencable->update([
                                        'paid' => true,
                                        'status' => StatusTransactionEnum::PAGO->value,
                                        'payment_forced' => true,
                                        'payment_forced_at' => now(),
                                    ]);
                                }
                            });

                            Notification::make()
                                ->title('Pagamento informado com sucesso!')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('editar_fatura')
                        ->label('Editar Fatura')
                        ->icon('heroicon-o-pencil')
                        ->visible(fn (Model $record) => $record->referencable_type === Transaction::class)
                        ->url(function (Model $record) {
                            return TransactionResource::getUrl('edit', ['record' => $record->referencable_id]);
                        })
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('Link de Pagamento')
                        ->icon('heroicon-o-credit-card')
                        ->url(function (Model $record) {
                            return "/admin/checkout/{$record->uuid}";
                        })
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('historico_cobranca')
                        ->label('Histórico de Cobrança')
                        ->icon('heroicon-o-clock')
                        ->modalHeading('Histórico de Cobrança')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fechar')
                        ->modalContent(fn (Model $record) => view('checkout-payment::partials.billing-history', [
                            'logs' => $record->billingLogs,
                        ])),

                    Tables\Actions\Action::make('ver_pagamento_mp')
                        ->label('Ver Pagamento MP')
                        ->icon('heroicon-o-magnifying-glass')
                        ->modalHeading('Dados do Pagamento MP')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fechar')
                        ->modalContent(function (Model $record) {
                            if ($record->gateway->name == TypeGatewayEnum::mercado_pago->value) {
                                $mps = new MercadoPagoService;
                                $pagamentos = $mps->buscarPagamentoPorExternalId($record->uuid);

                                if (empty($pagamentos)) {
                                    Notification::make('errors_mp')
                                        ->persistent()
                                        ->danger()
                                        ->title('Erro!!')
                                        ->body('Nenhum pagamento encontrado.')
                                        ->send();

                                    return view('checkout-payment::partials.empty', [
                                        'message' => 'Nenhum pagamento do mercado pago encontrado.',
                                        'erro' => $record->lastGenerationError(),
                                        'recordId' => $record->id,
                                    ]);
                                }

                                // status finais do MP que refletem no status do checkout, em ordem de prioridade.
                                // pagamentos ainda em aberto (pending, in_process, authorized, etc.) não
                                // atualizam o checkout, mas continuam sendo exibidos no modal abaixo.
                                $statusParaCheckout = [
                                    'approved' => StatusCheckoutEnum::finalizado->value,
                                    'cancelled' => StatusCheckoutEnum::cancelado->value,
                                    'rejected' => StatusCheckoutEnum::rejeitado->value,
                                    'refunded' => StatusCheckoutEnum::refunded->value,
                                ];

                                if (! $record->isPaymentForced()) {
                                    foreach ($statusParaCheckout as $statusMp => $statusCheckout) {
                                        if (collect($pagamentos)->firstWhere('status', $statusMp)) {
                                            $record->update([
                                                'startOnStep' => TypeStepEnum::finalizado->value,
                                                'status' => $statusCheckout,
                                            ]);

                                            break;
                                        }
                                    }
                                }

                                return view('checkout-payment::partials.pagamento-mp', [
                                    'pagamentos' => $pagamentos,
                                    'record' => $record,
                                ]);
                            }

                            return view('checkout-payment::partials.empty', [
                                'message' => 'Nenhum pagamento do MP encontrado.',
                            ]);
                        }),

                    /*Tables\Actions\Action::make('ver_pagamento_mp')
                        ->label('Ver Pagamento MP')
                        ->icon('heroicon-o-magnifying-glass')
                        ->modalHeading('Dados do Pagamento MP')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fechar')
                        ->modalContent(function (Model $record) {

                            if ($record->gateway->name !== TypeGatewayEnum::mercado_pago->value) {
                                return view('checkout-payment::partials.empty', [
                                    'message' => 'Nenhum pagamento do Mercado Pago encontrado.',
                                ]);
                            }

                            $mps = new MercadoPagoService;
                            $pagamentos = $mps->buscarPagamentoPorExternalId($record->uuid);

                            if (empty($pagamentos)) {
                                Notification::make('errors_mp')
                                    ->persistent()
                                    ->danger()
                                    ->title('Erro!')
                                    ->body('Nenhum pagamento encontrado.')
                                    ->send();

                                return view('checkout-payment::partials.empty', [
                                    'message' => 'Nenhum pagamento do Mercado Pago encontrado.',
                                ]);
                            }

                            // Pega o pagamento mais recente
                            $pagamento = collect($pagamentos)
                                ->sortByDesc(fn ($item) => $item['date_created'] ?? $item['id'] ?? 0)
                                ->first();

                            switch ($pagamento['status']) {

                                // Pagamento concluído
                                case 'approved':
                                    $record->update([
                                        'startOnStep' => TypeStepEnum::finalizado->value,
                                        'status'      => StatusCheckoutEnum::finalizado->value,
                                    ]);
                                    break;

                                // Ainda aguardando definição
                                case 'pending':
                                case 'in_process':
                                case 'authorized':
                                    $record->update([
                                        'status' => StatusCheckoutEnum::pendente->value,
                                    ]);
                                    break;

                                // Pagamentos cancelados/rejeitados
                                case 'cancelled':
                                case 'rejected':
                                case 'refunded':
                                case 'charged_back':
                                    $record->update([
                                        'status' => StatusCheckoutEnum::cancelado->value,
                                    ]);
                                    break;

                                // Qualquer outro status desconhecido
                                default:
                                    $record->update([
                                        'status' => StatusCheckoutEnum::pendente->value,
                                    ]);
                                    break;
                            }

                            return view('checkout-payment::partials.pagamento-mp', [
                                'pagamentos' => $pagamentos,
                                'record'     => $record,
                            ]);
                        }),*/

                    Tables\Actions\Action::make('ver_pagamento_sicoob')
                        ->label('Ver Pagamento Sicoob')
                        ->icon('heroicon-o-magnifying-glass')
                        ->modalHeading('Dados do Pagamento Sicoob')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fechar')
                        ->modalContent(function (Model $record) {

                            try {
                                $boletoPixSicoob = new BoletoPixService;
                                $consultar = $boletoPixSicoob->consult($record);
                            } catch (\Throwable $e) {
                                return view('checkout-payment::partials.empty', [
                                    'message' => 'Não foi possível consultar o Sicoob.',
                                    'erro' => ['message' => $e->getMessage(), 'code' => null],
                                    'recordId' => $record->id,
                                ]);
                            }

                            $status = $consultar['resultado']['situacaoBoleto'] ?? null;

                            $pagamentos = [];

                            if (isset($status) && $status == 'Liquidado') {
                                if (! $record->isPaymentForced()) {
                                    $record->update([
                                        'startOnStep' => TypeStepEnum::finalizado->value,
                                        'status' => StatusCheckoutEnum::finalizado->value,
                                    ]);
                                }
                                $pagamentos = [$consultar['resultado']];
                            }

                            if (isset($status) && $status == 'Baixado') {
                                if (! $record->isPaymentForced()) {
                                    $record->update([
                                        'startOnStep' => TypeStepEnum::finalizado->value,
                                        'status' => StatusCheckoutEnum::baixado->value,
                                    ]);
                                }
                                $pagamentos = [$consultar['resultado']];
                            }

                            if (isset($status) && $status == 'Em Aberto') {
                                if (! $record->isPaymentForced()) {
                                    $record->update([
                                        'startOnStep' => TypeStepEnum::finalizado->value,
                                        'status' => StatusCheckoutEnum::pendente->value,
                                    ]);
                                }
                                $pagamentos = [$consultar['resultado']];
                            }

                            if (count($pagamentos) > 0) {
                                return view('checkout-payment::partials.pagamento-sicoob', [
                                    'pagamentos' => $pagamentos,
                                    'record' => $record,
                                ]);
                            }

                            return view('checkout-payment::partials.empty', [
                                'message' => 'Nenhum pagamento do sicoob encontrado.',
                                'erro' => $record->lastGenerationError(),
                                'recordId' => $record->id,
                            ]);
                        }),

                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function informarPagamentoLabel(Model $record): string
    {
        return $record->payment_forced ? 'Editar Pagamento Informado' : 'Informar Pagamento';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCppCheckouts::route('/'),
            // 'create' => CreateCppCheckout::route('/create'),
            // 'edit'   => EditCppCheckout::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return config()->get('checkout-payment.sidebar_group');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check();
    }
}
