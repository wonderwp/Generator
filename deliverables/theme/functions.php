<?php
use __THEME_NS__\__THEME_ENTITY__Manager;

if (!function_exists('wwp_theme_setup')) {
    function wwp_theme_setup()
    {
        get_template_part('/includes/ThemeManager');

        $manager = new __THEME_ENTITY__Manager();
        $manager->run();
    }
}
wwp_theme_setup();