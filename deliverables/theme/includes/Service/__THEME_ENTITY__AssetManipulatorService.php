<?php

namespace __THEME_NS__\Service;

use Symfony\Component\HttpFoundation\ParameterBag;
use WonderWp\Component\Asset\DirectAssetEnqueuer;
use WonderWp\Component\DependencyInjection\Container;

class __THEME_ENTITY__AssetManipulatorService
{
    public function __construct()
    {
        $this->registerAssetEnqueuer();
    }

    public function registerAssetEnqueuer()
    {
        $container = Container::getInstance();

        //Assets
        $container['wwp.asset.enqueuer'] = function ($container) {
            $fileSystem = $container['wwp.fileSystem'];
            $publicPath = ROOT_DIR . str_replace('.', '', $container['wwp.asset.folder.prefix']);
            return new DirectAssetEnqueuer(
                $container['wwp.asset.manager'],
                $fileSystem,
                $publicPath,
            );
        };
    }

    public function enqueueFrontAssets()
    {
        $container = Container::getInstance();
        if ($container->offsetExists('wwp.asset.enqueuer')) {
            $groupNames     = ['app'];
            $assetsEnqueuer = $container->offsetGet('wwp.asset.enqueuer');
            $assetsEnqueuer->enqueueStyleGroups($groupNames);
            $assetsEnqueuer->enqueueScriptGroups($groupNames);
        }
    }

    public function addTypeModule(string $tag, string $handle, string $src)
    {
        if (strpos($handle, 'wwp_modern', 0) !== false) {
            return '<script type="module" src="' . $src . '"></script>';
        } elseif (strpos($handle, 'wwp_default', 0) !== false) {
            return '<script nomodule src="' . $src . '"></script>';
        }

        return $tag;
    }

    public function jsConfig()
    {
        /** @var ParameterBag $jsConfig */
        $jsConfig = Container::getInstance()->offsetGet('jsConfig');
        $jsConfig->add(['ajaxurl' => admin_url('admin-ajax.php')]);
        echo "\n" . '<script>window.wonderwp = window.wonderwp || ' . json_encode(apply_filters('wwp.jsconfig.render', $jsConfig->all())) . ';</script>' . "\n";
    }

}
