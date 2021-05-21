<?php

namespace __PLUGIN_NS__\CPT;

use WonderWp\Component\CPT\CustomPostType;
use function WonderWp\Functions\array_merge_recursive_distinct;

class __PLUGIN_ENTITY__CPT extends CustomPostType
{
    /**
     * __PLUGIN_ENTITY__CPT CPT constructor.
     *
     * @param string $name
     * @param array  $passed_opts CPT options, @see https://developer.wordpress.org/reference/functions/register_post_type/
     * @param string $taxonomy_name
     * @param array  $passed_taxonomy_opts
     */
    public function __construct($name = '', array $passed_opts = [], $taxonomy_name = '', array $passed_taxonomy_opts = [])
    {
        $name        = WWP_PLUGIN___PLUGIN_CONST___NAME;
        $defaultOpts = [
            'labels'       => [
                //CPT labels can be edited below
                'name' => ucfirst(__($name, WWP___PLUGIN_CONST___TEXTDOMAIN)),
                //'not_found'    => __($name . ' not found', WWP___PLUGIN_CONST___TEXTDOMAIN),
                //'add_new_item' => __('Add new ' . $name, WWP___PLUGIN_CONST___TEXTDOMAIN),
                //'edit_item'    => __('Edit ' . $name, WWP___PLUGIN_CONST___TEXTDOMAIN),
            ],
            'show_in_rest' => true,
            'rewrite'      => ['slug' => __(sanitize_title($name), WWP___PLUGIN_CONST___TEXTDOMAIN)],
        ];
        $opts        = array_merge_recursive_distinct($defaultOpts, $passed_opts);
        parent::__construct($name, $opts, $taxonomy_name, $passed_taxonomy_opts);
    }
}
