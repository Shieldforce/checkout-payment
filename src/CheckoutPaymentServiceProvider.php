<?php

namespace Shieldforce\CheckoutPayment;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Shieldforce\CheckoutPayment\Commands\CheckoutPaymentCommand;
use Shieldforce\CheckoutPayment\Testing\TestsCheckoutPayment;
use Filament\Panel;

class CheckoutPaymentServiceProvider extends PackageServiceProvider
{
    public static string $name = 'checkout-payment';

    public static string $viewNamespace = 'checkout-payment';

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('checkout-payment')
            ->path('checkout-payment')
            ->resources([
                // ...
            ])
            ->pages([
                \Shieldforce\CheckoutPayment\Pages\CheckoutWizard::class,
            ])
            ->widgets([
                // ...
            ])
            ->middleware([
                // ...
            ])
            ->authMiddleware([
                // ...
            ]);
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('shieldforce/checkout-payment');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/checkout-payment/{$file->getFilename()}"),
                ], 'checkout-payment-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsCheckoutPayment);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'shieldforce/checkout-payment';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('checkout-payment', __DIR__ . '/../resources/dist/components/checkout-payment.js'),
            Css::make('checkout-payment-styles', __DIR__ . '/../resources/dist/checkout-payment.css'),
            Js::make('checkout-payment-scripts', __DIR__ . '/../resources/dist/checkout-payment.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            CheckoutPaymentCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_checkout-payment_table',
        ];
    }
}
