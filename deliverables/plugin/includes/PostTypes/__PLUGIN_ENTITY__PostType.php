<?php

namespace __PLUGIN_NS__\CPT;

use WonderWp\Component\CPT\Traits\HasCustomTypeDefinitions;
use WonderWp\Component\CPT\Traits\HasCustomTypeDefinitionsInterface;
use WonderWp\Component\CPT\Traits\HasLabels;
use WonderWp\Component\CPT\Traits\HasLabelsInterface;
use WonderWp\Component\CPT\Traits\HasSupportInterface;
use WonderWp\Plugin\Core\Framework\AbstractPlugin\PostTypes\AbstractCustomPostType;

class __PLUGIN_ENTITY__PostType extends AbstractCustomPostType implements HasCustomTypeDefinitionsInterface, HasLabelsInterface
{
    use HasCustomTypeDefinitions;
    use HasLabels;

    public static function provideKey(): string
    {
        return WWP_PLUGIN___PLUGIN_CONST___NAME;
    }

    public static function provideArgs(): array
    {
        return [
            'labels' => static::provideLabels(),
            'public' => true,
            'hierarchical' => false,
            'has_archive' => false,
            'show_in_rest' => true,
            'supports' => [
                HasSupportInterface::TITLE,
                HasSupportInterface::EDITOR,
                HasSupportInterface::THUMBNAIL,
                HasSupportInterface::EXCERPT,
                HasSupportInterface::CUSTOM_FIELDS
            ],
            'rewrite' => [
                'slug' => '__PLUGIN_SLUG__'
            ],
        ];
    }

    public static function provideLabels(): array
    {
        $name = '__PLUGIN_CONST__';
        static::$labels = [
            //CPT labels can be edited below
            'name' => ucfirst(__($name, WWP___PLUGIN_CONST___TEXTDOMAIN)),
            //'not_found'    => __($name . ' not found', WWP___PLUGIN_CONST___TEXTDOMAIN),
            //'add_new_item' => __('Add new ' . $name, WWP___PLUGIN_CONST___TEXTDOMAIN),
            //'edit_item'    => __('Edit ' . $name, WWP___PLUGIN_CONST___TEXTDOMAIN),
        ];

        return static::getLabels();
    }
}
