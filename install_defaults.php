<?php

if (isset($_SERVER['PHP_SELF']) && strpos(strtolower($_SERVER['PHP_SELF']), 'install_defaults.php') !== false) {
    die('This file cannot be used on its own!');
}

global $_FORMS_DEFAULT;
$_FORMS_DEFAULT = array();
$_FORMS_DEFAULT['default_recipient'] = '';
$_FORMS_DEFAULT['min_submit_seconds'] = 3;

function forms_config_item_exists($name)
{
    global $_TABLES;

    if (!isset($_TABLES['conf_values'])) {
        return false;
    }

    return DB_count(
        $_TABLES['conf_values'],
        array('name', 'group_name'),
        array($name, 'forms')
    ) > 0;
}

function plugin_initconfig_forms()
{
    global $_FORMS_DEFAULT, $_TABLES;

    $c = config::get_instance();

    if (!$c->group_exists('forms')) {
        $c->add('sg_0', NULL, 'subgroup', 0, 0, NULL, 0, true, 'forms');
        $c->add('tab_main', NULL, 'tab', 0, 0, NULL, 0, true, 'forms', 0);
        $c->add('fs_01', NULL, 'fieldset', 0, 0, NULL, 0, true, 'forms', 0);
        $c->add('default_recipient', $_FORMS_DEFAULT['default_recipient'], 'text', 0, 0, 0, 10, true, 'forms', 0);
        $c->add('min_submit_seconds', $_FORMS_DEFAULT['min_submit_seconds'], 'text', 0, 0, 0, 20, true, 'forms', 0);
        return true;
    }

    // Repair installations created by early 0.1.x test releases.
    if (!forms_config_item_exists('tab_main')) {
        $c->add('tab_main', NULL, 'tab', 0, 0, NULL, 0, true, 'forms', 0);
    }
    if (!forms_config_item_exists('fs_01')) {
        $c->add('fs_01', NULL, 'fieldset', 0, 0, NULL, 0, true, 'forms', 0);
    }
    if (!forms_config_item_exists('default_recipient')) {
        $c->add('default_recipient', $_FORMS_DEFAULT['default_recipient'], 'text', 0, 0, 0, 10, true, 'forms', 0);
    }
    if (!forms_config_item_exists('min_submit_seconds')) {
        $c->add('min_submit_seconds', $_FORMS_DEFAULT['min_submit_seconds'], 'text', 0, 0, 0, 20, true, 'forms', 0);
    }
    // 0.1.4 removes the obsolete public-menu option: Forms has no catalogue.
    // Remove the obsolete 0.1.x menu option even if an earlier upgrade did not run.
    // Use an explicit DELETE because this must also repair partially upgraded installs.
    DB_query("DELETE FROM {$_TABLES['conf_values']} WHERE name='show_menu_entry' AND group_name='forms'", 1);
    if (DB_error()) {
        COM_errorLog('Forms Plugin: failed to remove obsolete show_menu_entry configuration value.');
        return false;
    }

    return true;
}
