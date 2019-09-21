<?php

declare(strict_types=1);

namespace PhpUnitGen\Console\Adapters\Laravel;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use PhpUnitGen\Console\Container\ConsoleContainerFactory;
use PhpUnitGen\Console\Contracts\Config\ConfigResolver;
use PhpUnitGen\Console\Contracts\Execution\Runner;

/**
 * Class PhpUnitGenServiceProvider.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class PhpUnitGenServiceProvider extends BaseServiceProvider
{
    /**
     * Add the binding for command.
     */
    public function register(): void
    {
        $this->app->bind(ConsoleContainerFactory::class, function () {
            return ConsoleContainerFactory::make();
        });
        $this->app->bind(PhpUnitGenCommand::class, function (Application $application) {
            return $application->get(ConsoleContainerFactory::class)->get(PhpUnitGenCommand::class);
        });
        $this->app->bind(CommandFinishedListener::class, function (Application $application) {
            $consoleContainer = $application->get(ConsoleContainerFactory::class);

            return new CommandFinishedListener(
                $application,
                $consoleContainer->get(ConfigResolver::class),
                $consoleContainer->get(PhpUnitGenCommand::class),
                $consoleContainer->get(Runner::class)
            );
        });
    }

    /**
     * Add the publishable configuration and register the command.
     *
     * @param Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher): void
    {
        $this->publishes([
            __DIR__.'/../../../config/phpunitgen.php' => $this->app->basePath('phpunitgen.php'),
        ], 'phpunitgen-config');

        $this->commands([
            PhpUnitGenCommand::class,
        ]);

        $dispatcher->listen(CommandFinished::class, CommandFinishedListener::class);
    }
}
