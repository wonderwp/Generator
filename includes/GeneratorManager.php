<?php

namespace WonderWp\Plugin\Generator;

use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\PluginSkeleton\AbstractPluginManager;
use WonderWp\Component\Service\ServiceInterface;
use WonderWp\Plugin\Generator\Service\GeneratorCommandService;
use WonderWp\Plugin\Generator\Service\GeneratorService;

class GeneratorManager extends AbstractPluginManager
{
    public function register(Container $container)
    {
        parent::register($container);

        $this->addService(ServiceInterface::COMMAND_SERVICE_NAME, function () {
            return new GeneratorCommandService();
        });

        $this->addService('generator', function () {
            return new GeneratorService();
        });

        return $this;
    }

}
