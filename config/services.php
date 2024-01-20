<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', __DIR__ . '/../src/')
        ->exclude([
            __DIR__ . '/../src/*/Domain/Entity/',
            __DIR__ . '/../src/Kernel.php',
        ]);

    $services->load('App\GitHubStatistics\Infrastructure\Controller\\', __DIR__ . '/../src/GitHubStatistics/Infrastructure/Controller/')
        ->tag('controller.service_arguments');

    $services->load('App\DataFixtures\\', '/%kernel.project_dir%/fixtures/*');
};
