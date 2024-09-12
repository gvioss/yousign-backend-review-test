<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\FrameworkConfig;

return static function (ContainerConfigurator $container, FrameworkConfig $framework): void {
    $framework->router()->utf8(true);

    if ($container->env() === 'prod') {
        $framework->router()->strictRequirements(null);
    }
};
