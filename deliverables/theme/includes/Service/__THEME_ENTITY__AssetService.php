<?php

namespace __THEME_NS__\Service;

use WonderWp\Component\Asset\AbstractAssetService;
use WonderWp\Component\Asset\Asset;

class __THEME_ENTITY__AssetService extends AbstractAssetService
{
    public function getAssets()
    {
        if (empty($this->_assets)) {
            $manager   = $this->manager;
            $themePath = $manager->getConfig('path.url');
            /** @var Asset $assetClass */
            $assetClass = self::$assetClassName;

            $this->_assets = [
                'css' => [
                    new $assetClass('theme', $themePath . '/style.css', [], '', true, 'app'),
                ],
                'js'  => [

                ],
            ];
        }

        return $this->_assets;
    }
}
