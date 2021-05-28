<?php

namespace WonderWp\Plugin\Generator;

use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\PluginSkeleton\AbstractPluginManager;
use WonderWp\Component\Service\ServiceInterface;
use WonderWp\Plugin\Generator\Service\Generator\Base\BaseGenerator;
use WonderWp\Plugin\Generator\Service\Generator\CPT\CPTGenerator;
use WonderWp\Plugin\Generator\Service\GeneratorCommandService;

class GeneratorManager extends AbstractPluginManager
{
    public function register(Container $container)
    {
        parent::register($container);

        //Register Config
        $this->setConfig('path.root', plugin_dir_path(dirname(__FILE__)));
        $this->setConfig('path.base', dirname(dirname(plugin_basename(__FILE__))));
        $this->setConfig('path.url', plugin_dir_url(dirname(__FILE__)));
        $this->setConfig('textDomain', WWP_GENERATOR_TEXTDOMAIN);

        $this->addService(ServiceInterface::COMMAND_SERVICE_NAME, function () {
            return new GeneratorCommandService();
        });

        $this->addService('generator', function () {
            return new BaseGenerator($this);
        });
        $this->addService('CPTGenerator', function () {
            return new CPTGenerator($this);
        });

        return $this;
    }

}
