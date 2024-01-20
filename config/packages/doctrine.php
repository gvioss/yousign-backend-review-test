<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\DoctrineConfig;
use Symfony\Config\FrameworkConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (ContainerConfigurator $container, DoctrineConfig $doctrine, FrameworkConfig $framework): void {

    $doctrine->dbal()->connection('default')
        ->url(env('DATABASE_URL'))
        ->schemaFilter('~^(?!messenger_messages)~');

    $doctrine->orm()->autoGenerateProxyClasses(true);

    $em = $doctrine->orm()->entityManager('default')
        ->autoMapping(true)
        ->namingStrategy('doctrine.orm.naming_strategy.underscore_number_aware');

    $em->mapping('App')
        ->isBundle(false)
        ->dir('%kernel.project_dir%/src/Entity')
        ->prefix('App\Entity')
        ->alias('App');

    if ($container->env() === 'test') {
        $doctrine->dbal()->connection('default')
            ->dbnameSuffix('_test');
    }

    if ($container->env() === 'prod') {
        $doctrine->orm()->autoGenerateProxyClasses(false);

        $em->queryCacheDriver()
            ->type('pool')
            ->pool('doctrine.system_cache_pool');
        $em->resultCacheDriver()
            ->type('pool')
            ->pool('doctrine.result_cache_pool');

        $framework->cache()
            ->pool('doctrine.result_cache_pool')
                ->adapters('cache.app')
            ->pool('doctrine.system_cache_pool')
                ->adapters('cache.system');
    }
};
