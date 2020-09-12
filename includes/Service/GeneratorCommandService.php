<?php

namespace WonderWp\Plugin\Generator\Service;

use WonderWp\Component\Task\TaskServiceInterface;
use WonderWp\Plugin\Generator\Command\PluginGeneratorCommand;

class GeneratorCommandService implements TaskServiceInterface
{
    const COMMAND_NAME = 'generate-plugin';

    /** @inheritdoc */
    public function register()
    {
        if (!class_exists('WP_CLI')) {
            return;
        }

        \WP_CLI::add_command(self::COMMAND_NAME, PluginGeneratorCommand::class, $this->getGeneratorCommandArgs());
    }

    protected function getGeneratorCommandArgs(){
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
            ],
            'longdesc'  => '## EXAMPLES' . "\n\n" . 'wp generate-plugin --name="myPluginName" --desc="This is my plugin description" --namespace="WonderWp\Plugin\MyPluginNameSpace"',
        ];
    }
}
