<?php

require_once dirname(__FILE__) . '/functions.inc';

function plugin_autoinstall_forms($pi_name)
{
    $piName = 'forms';
    $displayName = 'Forms';
    $adminGroup = 'Forms Admin';

    return array(
        'info' => array(
            'pi_name'         => $piName,
            'pi_display_name' => $displayName,
            'pi_version'      => '0.3.2',
            'pi_gl_version'   => '2.1.1',
            'pi_homepage'     => 'https://www.geeklog.net/'
        ),
        'groups' => array(
            $adminGroup => 'Users in this group can administer the Forms plugin'
        ),
        'features' => array(
            'forms.admin' => 'Full access to the Forms plugin',
            'config.forms.tab_main' => 'Access to Forms configuration'
        ),
        'mappings' => array(
            'forms.admin' => array($adminGroup),
            'config.forms.tab_main' => array($adminGroup)
        ),
        'tables' => array(
            'forms_definitions',
            'forms_fields',
            'forms_submissions',
            'forms_submission_values'
        )
    );
}

function plugin_load_configuration_forms($pi_name)
{
    global $_CONF;

    $defaults = $_CONF['path'] . 'plugins/' . $pi_name . '/install_defaults.php';
    if (!file_exists($defaults)) {
        return false;
    }

    require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once $defaults;

    return function_exists('plugin_initconfig_forms') && plugin_initconfig_forms();
}

function plugin_compatible_with_this_version_forms($pi_name)
{
    global $_CONF, $_DB_dbms;

    if (!defined('VERSION')) {
        return false;
    }

    if (function_exists('COM_versionCompare')) {
        if (!COM_versionCompare(VERSION, '2.1.1', '>=')) {
            return false;
        }
    } elseif (version_compare(VERSION, '2.1.1', '<')) {
        return false;
    }

    if (version_compare(PHP_VERSION, '5.6.0', '<')) {
        return false;
    }

    $dbms = strtolower((string) $_DB_dbms);
    if ($dbms === 'mysqli') {
        $dbms = 'mysql';
    }

    $dbFile = $_CONF['path'] . 'plugins/' . $pi_name . '/sql/' . $dbms . '_install.php';
    return file_exists($dbFile);
}
