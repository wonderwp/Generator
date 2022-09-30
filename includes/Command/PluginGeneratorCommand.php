<?php

namespace WonderWp\Plugin\Generator\Command;

use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Logging\LoggerInterface;
use WonderWp\Component\Logging\WpCliLogger;
use WonderWp\Component\PluginSkeleton\Exception\ServiceNotFoundException;
use WonderWp\Plugin\Generator\GeneratorManager;
use WonderWp\Plugin\Generator\Generator\GeneratorInterface;

class PluginGeneratorCommand
{
    const COMMAND_NAME = 'generate-plugin';

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

    public static function getArgsDefinition()
    {
        return
            [
                'shortdesc' => 'WonderWp plugin generation.',
                'synopsis'  => [
                    [
                        'name'        => 'name',
                        'description' => '(required) The name of your plugin, which will be displayed in the Plugins list in the WordPress Admin.',
                        'type'        => 'assoc',
                        'optional'    => false,
                    ],
                    [
                        'name'        => 'namespace',
                        'description' => 'Plugin php object namespace.',
                        'type'        => 'assoc',
                        'optional'    => false,
                    ],
                    [
                        'name'        => 'desc',
                        'description' => 'A short description of the plugin, as displayed in the Plugins section in the WordPress Admin. Keep this description to fewer than 140 characters.',
                        'type'        => 'assoc',
                        'optional'    => true,
                    ],
                    [
                        'name'        => 'uri',
                        'description' => 'The home page of the plugin, which should be a unique URL, preferably on your own website. This must be unique to your plugin. You cannot use a WordPress.org URL here.',
                        'type'        => 'assoc',
                        'optional'    => true,
                    ],
                    [
                        'name'        => 'author',
                        'description' => 'The name of the plugin author. Multiple authors may be listed using commas.',
                        'type'        => 'assoc',
                        'optional'    => true,
                    ],
                    [
                        'name'        => 'author_uri',
                        'description' => 'The author’s website or profile on another website, such as WordPress.org.',
                        'type'        => 'assoc',
                        'optional'    => true,
                    ],
                    [
                        'name'        => 'version',
                        'description' => 'The current version number of the plugin, such as 1.0 or 1.0.3.',
                        'type'        => 'assoc',
                        'optional'    => true,
                    ],
                    [
                        'name'        => 'licence',
                        'description' => 'The short name (slug) of the plugin’s license (e.g. GPL2). More information about licensing can be found in the WordPress.org guidelines.',
                        'type'        => 'assoc',
                        'optional'    => true,
                    ],
                    [
                        'name'        => 'licence_uri',
                        'description' => 'A link to the full text of the license (e.g. https://www.gnu.org/licenses/gpl-2.0.html).',
                        'type'        => 'assoc',
                        'optional'    => true,
                    ],
                    [
                        'name'        => 'textdomain',
                        'description' => 'The gettext text domain of the plugin. More information can be found in the Text Domain section of the How to Internationalize your Plugin page.',
                        'type'        => 'assoc',
                        'optional'    => true,
                    ],
                    [
                        'name'        => 'domain_path',
                        'description' => ' The domain path let WordPress know where to find the translations. More information can be found in the Domain Path section of the How to Internationalize your Plugin page.',
                        'type'        => 'assoc',
                        'optional'    => true,
                    ],
                    [
                        'name'        => 'output_type',
                        'description' => 'Leave empty for a bare plugin architecture, set CPT for a custom post type generation.',
                        'type'        => 'assoc',
                        'optional'    => true,
                    ],
                ],
                'longdesc'  => '## EXAMPLES' . "\n\n" . 'wp generate-plugin --name="myPluginName" --desc="This is my plugin description" --namespace="WonderWp\Plugin\MyPluginNameSpace"',
            ];
    }
}
