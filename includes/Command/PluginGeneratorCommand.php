<?php

namespace WonderWp\Plugin\Generator\Command;

use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Logging\LoggerInterface;
use WonderWp\Component\Logging\WpCliLogger;
use WonderWp\Component\PluginSkeleton\Exception\ServiceNotFoundException;
use WonderWp\Plugin\Generator\GeneratorManager;
use WonderWp\Plugin\Generator\Service\Generator\GeneratorInterface;

class PluginGeneratorCommand
{
    protected static $managerReference = WWP_PLUGIN_GENERATOR_NAME . '.Manager';
    /** @var LoggerInterface */
    protected $logger;

    public function __invoke(array $args = [], array $assocArgs = [])
    {
        $container = Container::getInstance();
        /** @var GeneratorManager $manager */
        $manager      = $container[static::$managerReference];
        $this->logger = new WpCliLogger();

        /** @var GeneratorInterface $generator */
        $type = !empty($assocArgs['output_type']) ? $assocArgs['output_type'] : '';
        if (!empty($type)) {
            try {
                $generator = $manager->getService($type . 'Generator');
            } catch (ServiceNotFoundException $e) {
                $this->logger->error($e->getMessage());

                return false;
            }
        } else {
            $type = "basic";
            try {
                $generator = $manager->getService('generator');
            } catch (ServiceNotFoundException $e) {
                $this->logger->error($e->getMessage());

                return false;
            }
        }

        $generator->setLogger($this->logger);

        if (!empty($assocArgs)) {
            $this->logger->info("Starting Generation of the `" . $assocArgs['name'] . "` plugin, which is a `$type` plugin");
            $this->logger->info("Based on type `$type`, using the `" . get_class($generator) . "` generator.");
            $generator->setData($assocArgs);
            $generationResult = $generator->generate();
            if ($generationResult->getCode() === 200) {
                $this->logger->success($generationResult->getData('msg'));
            } else {
                $this->logger->error($generationResult->getData('msg'), ['exit' => false]);
                $exception = $generationResult->getData('exception');
                if (!empty($exception)) {
                    if (empty($assocArgs['debug']) || $assocArgs['debug'] !== 'generator') {
                        $this->logger->info('More information about the error can be seen by adding the --debug=generator flag to your command.');
                    }
                    $this->logger->debug(print_r($exception, true), ['group' => 'generator']);
                }
            }
        } else {
            $this->logger->error('No data given to the generator. Not enough context provided to generate a plugin');
        }

        return true;
    }
}
