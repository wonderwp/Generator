<?php

namespace WonderWp\Plugin\Generator\Command;

use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Logging\LoggerInterface;
use WonderWp\Component\Logging\WpCliLogger;
use WonderWp\Component\PluginSkeleton\Exception\ServiceNotFoundException;
use WonderWp\Plugin\Generator\Generator\GeneratorInterface;
use WonderWp\Plugin\Generator\GeneratorManager;

class ThemeGeneratorCommand
{
    const COMMAND_NAME = 'generate-theme';

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
        $type = !empty($assocArgs['output_type']) ? $assocArgs['output_type'] : 'classic';

        try {
            $generator = $manager->getService($type . 'ThemeGenerator');
        } catch (ServiceNotFoundException $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        $generator->setLogger($this->logger);

        if (!empty($assocArgs)) {
            $this->logger->info("Starting Generation of the `" . $assocArgs['name'] . "` theme, which is a `$type` theme");
            $this->logger->info("Based on type `$type`, using the `" . get_class($generator) . "` generator.");
            $generator->setData($assocArgs);
            $generationResult = $generator->generate();
            if ($generationResult->getCode() === 200) {
                $this->logger->success($generationResult->getData('msg'));
            } else {
                $this->logger->error($generationResult->getData('msg'));
                $this->logger->debug($generationResult);
            }
        } else {
            $this->logger->error('No data given to the generator. Not enough context provided to generate a theme');
        }

        return true;
    }

    public static function getArgsDefinition()
    {
        return [
            'shortdesc' => 'WonderWp theme generation.',
            'synopsis'  => [
                [
                    'name'        => 'name',
                    'description' => '(required) The name of your theme, which will be displayed in the themes list in the WordPress Admin.',
                    'type'        => 'assoc',
                    'optional'    => false,
                ],
                [
                    'name'        => 'namespace',
                    'description' => 'Theme php object namespace.',
                    'type'        => 'assoc',
                    'optional'    => false,
                ],
                [
                    'name'        => 'desc',
                    'description' => 'A short description of the theme, as displayed in the Themes section in the WordPress Admin. Keep this description to fewer than 140 characters.',
                    'type'        => 'assoc',
                    'optional'    => true,
                ],
                [
                    'name'        => 'uri',
                    'description' => 'The home page of the theme, which should be a unique URL, preferably on your own website. This must be unique to your theme. You cannot use a WordPress.org URL here.',
                    'type'        => 'assoc',
                    'optional'    => true,
                ],
                [
                    'name'        => 'author',
                    'description' => 'The name of the theme author. Multiple authors may be listed using commas.',
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
                    'description' => 'The current version number of the theme, such as 1.0 or 1.0.3.',
                    'type'        => 'assoc',
                    'optional'    => true,
                ],
                [
                    'name'        => 'licence',
                    'description' => 'The short name (slug) of the theme’s license (e.g. GPL2). More information about licensing can be found in the WordPress.org guidelines.',
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
                    'description' => 'The gettext text domain of the theme. More information can be found in the Text Domain section of the How to Internationalize your Theme page.',
                    'type'        => 'assoc',
                    'optional'    => true,
                ],
                [
                    'name'        => 'domain_path',
                    'description' => ' The domain path let WordPress know where to find the translations. More information can be found in the Domain Path section of the How to Internationalize your Theme page.',
                    'type'        => 'assoc',
                    'optional'    => true,
                ],
                [
                    'name'        => 'output_type',
                    'description' => 'Leave empty for a classic theme architecture, set block for a block theme generation.',
                    'type'        => 'assoc',
                    'optional'    => true,
                ],
            ],
        ];
    }
}