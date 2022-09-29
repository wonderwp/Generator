<?php

namespace WonderWp\Plugin\Generator\Generator\Theme\Classic;

use Exception;
use WonderWp\Component\PluginSkeleton\AbstractManager;
use WonderWp\Plugin\Generator\Generator\Definition\AbstractGenerator;
use WonderWp\Plugin\Generator\Generator\Theme\Classic\ContentProvider\ClassicHookServiceContentProvider;
use WonderWp\Plugin\Generator\Generator\Theme\Classic\ContentProvider\ClassicManagerContentProvider;
use WonderWp\Plugin\Generator\Result\DataCheckResult;
use WonderWp\Plugin\Generator\Result\GenerationResult;
use WP_Error;
use WP_Filesystem_Direct;
use function WonderWp\Functions\array_merge_recursive_distinct;

class ClassicThemeGenerator extends AbstractGenerator
{
    //Content Providers
    /** @var ClassicManagerContentProvider */
    protected $managerContentProvider;
    /** @var ClassicHookServiceContentProvider */
    protected $hookServiceContentProvider;

    public function __construct(AbstractManager $manager)
    {
        parent::__construct($manager);
        $this->managerContentProvider     = new ClassicManagerContentProvider();
        $this->hookServiceContentProvider = new ClassicHookServiceContentProvider();
    }


    public function generate(): GenerationResult
    {
        $check = $this->checkDatas();

        if ($check->getCode() === 200) {
            try {
                $this
                    ->prepareDatas()
                    ->createBaseFolders()
                    ->generateIndexFile()
                    ->generateCssFile()
                    ->generateFunctionsFile()
                    ->generatePageFile()
                    ->generatePageContentPartFile()
                    ->generateHeaderFile()
                    ->generateFooterFile()
                    ->generateManager()
                    ->generateHookService()
            } catch (Exception $e) {
                return new GenerationResult(500, ['msg' => $e->getMessage(), 'exception' => $e]);
            }

            return new GenerationResult(200, [
                'msg' => "
Theme generated in your themes folder.
Don't forget to add an autoload entry in your composer.json file, then to launch a composer dump-autoload command, if you'd like to use it right away.
More information in the documentation : http://wonderwp.net/Creating_a_theme/Getting_Started
",
            ]);
        } else {
            $errors = $check->getData('errors');

            return new GenerationResult(500, ['msg' => implode("\n", $errors), 'errors' => $errors]);
        }
    }

    protected function checkDatas()
    {
        $requiredDatas = ['name', 'namespace'];
        $errors        = [];
        foreach ($requiredDatas as $req) {
            if (empty($this->data[$req])) {
                $errors[$req] = 'Attribute ' . $req . ' is missing';
            }
        }
        $code = empty($errors) ? 200 : 403;

        return new DataCheckResult($code, ['datas' => $this->data, 'errors' => $errors]);
    }


    protected function createBaseFolders(array $folders = [])
    {
        $this->folders                   = $folders;
        $this->folders['base']           = WP_CONTENT_DIR . '/themes/' . sanitize_title($this->data['name']);
        $this->folders['assets']         = $this->folders['base'] . '/assets';
        $this->folders['includes']       = $this->folders['base'] . '/includes';
        $this->folders['services']       = $this->folders['includes'] . '/Service';
        $this->folders['template-parts'] = $this->folders['base'] . '/template-parts';
        $this->folders['page']           = $this->folders['template-parts'] . '/page';
        $this->folders['languages']      = $this->folders['base'] . '/languages';
        $errors                          = [];

        foreach ($this->folders as $folder) {
            if (!is_dir($folder)) {
                if (!$this->fileSystem->mkdir($folder, FS_CHMOD_DIR)) {
                    $errors[] = new WP_Error(500, 'Required folder creation failed: ' . $folder);
                }
            }
        }

        if (!empty($errors)) {
            throw new Exception('Theme base folders generation error : ' . implode("\n", $errors));
        }

        return $this;
    }

    protected function generateIndexFile()
    {
        $this->importDeliverable('index.php', [], 'theme');

        return $this;
    }

    protected function generateCssFile()
    {
        $themeMetas = [];

        $themeMetas['Theme Name'] = $this->data['name'];
        if (!empty($this->data['uri'])) {
            $themeMetas['Theme URI'] = $this->data['uri'];
        }
        if (!empty($this->data['desc'])) {
            $themeMetas['Description'] = $this->data['desc'];
        }
        $themeMetas['Version'] = !empty($this->data['version']) ? $this->data['version'] : '0.0.1';
        if (!empty($this->data['author'])) {
            $themeMetas['Author'] = $this->data['author'];
        }
        if (!empty($this->data['author_uri'])) {
            $themeMetas['Author URI'] = $this->data['author_uri'];
        }
        if (!empty($this->data['licence'])) {
            $themeMetas['License'] = $this->data['licence'];
        }
        if (!empty($this->data['licence_uri'])) {
            $themeMetas['License URI'] = $this->data['licence_uri'];
        }

        $themeMetas['Text Domain'] = !empty($this->data['textdomain']) ? $this->data['textdomain'] : strtolower($this->data['className']);
        $themeMetas['Domain Path'] = !empty($this->data['domain_path']) ? $this->data['domain_path'] : '/languages';

        $themeMetasString = '';
        foreach ($themeMetas as $key => $val) {
            $themeMetasString .= "\n" . ' * ' . $key . ': ' . $val;
        }

        $this->importDeliverable('style.css', [
            '__THEME_METAS__' => $themeMetasString,
        ], 'theme');

        return $this;
    }

    protected function generateFunctionsFile(array $givenReplacements = [])
    {
        $baseReplacements = [];

        $this->importDeliverable('functions.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements), 'theme');

        return $this;
    }

    protected function generatePageFile(array $givenReplacements = [])
    {
        $baseReplacements = [];

        $this->importDeliverable('page.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements), 'theme');

        return $this;
    }

    protected function generatePageContentPartFile(array $givenReplacements = [])
    {
        $baseReplacements = [
            '__THEME_TEXTDOMAIN__' => !empty($this->data['textdomain']) ? $this->data['textdomain'] : strtolower($this->data['className'])
        ];

        $this->importDeliverable('template-parts' . DIRECTORY_SEPARATOR . 'page' . DIRECTORY_SEPARATOR . 'content-page.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements), 'theme');

        return $this;
    }

    protected function generateHeaderFile(array $givenReplacements = [])
    {
        $baseReplacements = [
            '__THEME_TEXTDOMAIN__' => !empty($this->data['textdomain']) ? $this->data['textdomain'] : strtolower($this->data['className'])
        ];

        $this->importDeliverable('header.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements), 'theme');

        return $this;
    }

    protected function generateFooterFile(array $givenReplacements = [])
    {
        $baseReplacements = [
            '__THEME_TEXTDOMAIN__' => !empty($this->data['textdomain']) ? $this->data['textdomain'] : strtolower($this->data['className'])
        ];

        $this->importDeliverable('footer.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements), 'theme');

        return $this;
    }

    protected function generateManager(array $givenReplacements = [])
    {
        $baseReplacements = [
            '//__MANAGER_EXTRA_USES//'           => $this->replacePlaceholders($this->managerContentProvider->getUsesContent()),
            '//__MANAGER_EXTRA_CONFIG__//'       => $this->replacePlaceholders($this->managerContentProvider->getConfigContent()),
            '//__MANAGER_EXTRA_SERVICES__//'     => $this->replacePlaceholders($this->managerContentProvider->getServicesContent()),
            '__THEME_PARENT_MANAGER_NAMESPACE__' => 'WonderWp\Component\PluginSkeleton\AbstractManager',
            '__THEME_PARENT_MANAGER__'           => 'AbstractManager',
            '__THEME_TEXTDOMAIN__'               => !empty($this->data['textdomain']) ? $this->data['textdomain'] : strtolower($this->data['className'])
        ];

        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . '__THEME_ENTITY__Manager.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements), 'theme');

        return $this;
    }

    protected function generateHookService(array $givenReplacements = [])
    {
        $baseReplacements = [
            '//__THEME_HOOKS_EXTRA_USES__//'         => $this->replacePlaceholders($this->hookServiceContentProvider->getUsesContent()),
            '//__THEME_HOOKS_EXTRA_DECLARATIONS__//' => $this->replacePlaceholders($this->hookServiceContentProvider->getHooksDeclarationsContent()),
            '//__THEME_HOOKS_EXTRA_CALLABLES__//'    => $this->replacePlaceholders($this->hookServiceContentProvider->getHooksCallablesContent()),
            '//__THEME_HOOKS_CLASS_ATTRIBUTES__//'   => $this->replacePlaceholders($this->hookServiceContentProvider->getHooksClassAttributes()),
        ];
        $this->importDeliverable('includes' . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . '__THEME_ENTITY__HookService.php', array_merge_recursive_distinct($baseReplacements, $givenReplacements), 'theme');

        return $this;
    }
        return $this;
    }

    /**
     * @param string $string
     * @param string[] $replacements
     *
     * @return string
     */
    protected function replacePlaceholders($string, array $replacements = [])
    {
        $replacements = array_merge([
            '__THEME_NAME__'       => $this->data['name'],
            '__THEME_SLUG__'       => sanitize_title($this->data['name']),
            '__THEME_DESC__'       => !empty($this->data['desc']) ? $this->data['desc'] : '',
            '__THEME_CONST__'      => strtoupper($this->data['classprefix']),
            '__THEME_CONST_LOW__'  => strtolower($this->data['classprefix']),
            '__THEME_ENTITY__'     => $this->data['classprefix'],
            '__THEME_NS__'         => $this->data['namespace'],
            '__THEME_CLASSNAME__'  => $this->data['className'],
            '__ESCAPED_THEME_NS__' => str_replace('\\', '\\\\', $this->data['namespace']),
        ], $replacements);

        return str_replace(array_keys($replacements), array_values($replacements), $string);
    }
}