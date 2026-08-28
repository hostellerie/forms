<?php

if (isset($_SERVER['PHP_SELF']) && strpos(strtolower($_SERVER['PHP_SELF']), 'functions.inc') !== false) {
    die('This file cannot be used on its own.');
}

global $_CONF, $_TABLES, $_FORMS_CONF, $_DB_table_prefix;

if (!isset($_DB_table_prefix)) {
    $_DB_table_prefix = '';
}
if (!isset($_TABLES['forms_definitions'])) {
    $_TABLES['forms_definitions'] = $_DB_table_prefix . 'forms_definitions';
}
if (!isset($_TABLES['forms_fields'])) {
    $_TABLES['forms_fields'] = $_DB_table_prefix . 'forms_fields';
}
if (!isset($_TABLES['forms_submissions'])) {
    $_TABLES['forms_submissions'] = $_DB_table_prefix . 'forms_submissions';
}
if (!isset($_TABLES['forms_submission_values'])) {
    $_TABLES['forms_submission_values'] = $_DB_table_prefix . 'forms_submission_values';
}

// One-time compatibility repair for early 0.1.x test builds. Some Geeklog
// upgrade paths can replace plugin files without immediately calling the
// plugin upgrade hook. Remove the obsolete menu setting as soon as Forms is
// loaded so Configuration cannot keep displaying it.
if (!defined('GL_INSTALL_ACTIVE') && isset($_TABLES['conf_values'])) {
    $formsObsoleteMenuCount = DB_count(
        $_TABLES['conf_values'],
        array('name', 'group_name'),
        array('show_menu_entry', 'forms')
    );
    if ($formsObsoleteMenuCount > 0) {
        DB_query("DELETE FROM {$_TABLES['conf_values']} WHERE name='show_menu_entry' AND group_name='forms'", 1);
        if (DB_error()) {
            COM_errorLog('Forms Plugin: failed to remove obsolete show_menu_entry configuration value.');
        }
    }
}

$formsPluginPath = $_CONF['path'] . 'plugins/forms/';
$formsLanguage = isset($_CONF['language']) ? $_CONF['language'] : 'english';
$formsLanguageFile = $formsPluginPath . 'language/' . $formsLanguage . '.php';
if (file_exists($formsLanguageFile)) {
    require_once $formsLanguageFile;
} else {
    require_once $formsPluginPath . 'language/english.php';
}

function forms_get_config($refresh = false)
{
    global $_CONF, $_FORMS_CONF;

    if ($refresh || !isset($_FORMS_CONF) || !is_array($_FORMS_CONF)) {
        require_once $_CONF['path_system'] . 'classes/config.class.php';
        $config = config::get_instance();
        $_FORMS_CONF = $config->get_config('forms');
    }

    return is_array($_FORMS_CONF) ? $_FORMS_CONF : array();
}

function forms_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function forms_slugify($value)
{
    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9_-]+/', '-', $value);
    $value = trim($value, '-');
    return substr($value, 0, 64);
}

function forms_get_form($slugOrId)
{
    global $_TABLES;

    if (is_numeric($slugOrId)) {
        $where = 'id = ' . (int) $slugOrId;
    } else {
        $where = "slug = '" . DB_escapeString($slugOrId) . "'";
    }

    $result = DB_query("SELECT * FROM {$_TABLES['forms_definitions']} WHERE $where LIMIT 1", 1);
    if (DB_error() || DB_numRows($result) < 1) {
        return false;
    }

    return DB_fetchArray($result);
}

function forms_get_fields($formId)
{
    global $_TABLES;

    $fields = array();
    $result = DB_query("SELECT * FROM {$_TABLES['forms_fields']} WHERE form_id = " . (int) $formId . " AND is_active = 1 ORDER BY field_order, id", 1);
    if (DB_error()) {
        return $fields;
    }
    while ($row = DB_fetchArray($result)) {
        $fields[] = $row;
    }
    return $fields;
}

function forms_parse_options($options)
{
    $out = array();
    $lines = preg_split('/\r\n|\r|\n/', (string) $options);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = explode('|', $line, 2);
        $value = trim($parts[0]);
        $label = isset($parts[1]) ? trim($parts[1]) : $value;
        $out[$value] = $label;
    }
    return $out;
}

function forms_render_field($field, $value, $error)
{
    global $LANG_FORMS;

    $name = forms_escape($field['name']);
    $label = forms_escape($field['label']);
    $type = strtolower($field['type']);
    $required = !empty($field['is_required']);
    $requiredMark = $required ? ' <span class="forms-required">*</span>' : '';
    $requiredAttr = $required ? ' required="required"' : '';
    $placeholder = !empty($field['placeholder']) ? ' placeholder="' . forms_escape($field['placeholder']) . '"' : '';
    $html = '<div class="forms-field forms-field-' . forms_escape($type) . ($error ? ' forms-error' : '') . '">';

    if ($type === 'heading') {
        $html .= '<h3>' . $label . '</h3>';
    } elseif ($type === 'info') {
        $html .= '<div class="forms-info">' . nl2br($label) . '</div>';
    } elseif ($type === 'textarea') {
        $html .= '<label for="forms-' . $name . '">' . $label . $requiredMark . '</label>';
        $html .= '<textarea id="forms-' . $name . '" name="fields[' . $name . ']" rows="6"' . $requiredAttr . $placeholder . '>' . forms_escape($value) . '</textarea>';
    } elseif ($type === 'select') {
        $html .= '<label for="forms-' . $name . '">' . $label . $requiredMark . '</label>';
        $html .= '<select id="forms-' . $name . '" name="fields[' . $name . ']"' . $requiredAttr . '><option value=""></option>';
        foreach (forms_parse_options($field['options']) as $optionValue => $optionLabel) {
            $selected = ((string) $value === (string) $optionValue) ? ' selected="selected"' : '';
            $html .= '<option value="' . forms_escape($optionValue) . '"' . $selected . '>' . forms_escape($optionLabel) . '</option>';
        }
        $html .= '</select>';
    } elseif ($type === 'radio') {
        $html .= '<div class="forms-label">' . $label . $requiredMark . '</div>';
        foreach (forms_parse_options($field['options']) as $optionValue => $optionLabel) {
            $checked = ((string) $value === (string) $optionValue) ? ' checked="checked"' : '';
            $html .= '<label class="forms-choice"><input type="radio" name="fields[' . $name . ']" value="' . forms_escape($optionValue) . '"' . $checked . $requiredAttr . '> ' . forms_escape($optionLabel) . '</label>';
        }
    } elseif ($type === 'checkboxes') {
        $html .= '<div class="forms-label">' . $label . $requiredMark . '</div>';
        $selectedValues = is_array($value) ? $value : (($value === '' || $value === null) ? array() : explode(', ', (string) $value));
        foreach (forms_parse_options($field['options']) as $optionValue => $optionLabel) {
            $checked = in_array((string) $optionValue, array_map('strval', $selectedValues), true) ? ' checked="checked"' : '';
            $html .= '<label class="forms-choice"><input type="checkbox" name="fields[' . $name . '][]" value="' . forms_escape($optionValue) . '"' . $checked . '> ' . forms_escape($optionLabel) . '</label>';
        }
    } elseif ($type === 'checkbox' || $type === 'consent') {
        $checked = !empty($value) ? ' checked="checked"' : '';
        $html .= '<label class="forms-choice"><input type="checkbox" id="forms-' . $name . '" name="fields[' . $name . ']" value="1"' . $checked . $requiredAttr . '> ' . $label . $requiredMark . '</label>';
    } else {
        $html .= '<label for="forms-' . $name . '">' . $label . $requiredMark . '</label>';
        $inputTypes = array('email'=>'email','date'=>'date','number'=>'number','phone'=>'tel','url'=>'url','time'=>'time','datetime'=>'datetime-local');
        $inputType = isset($inputTypes[$type]) ? $inputTypes[$type] : 'text';
        $html .= '<input type="' . $inputType . '" id="forms-' . $name . '" name="fields[' . $name . ']" value="' . forms_escape($value) . '"' . $requiredAttr . $placeholder . '>';
    }

    if (!empty($field['help_text'])) {
        $html .= '<div class="forms-help">' . nl2br(forms_escape($field['help_text'])) . '</div>';
    }
    if ($error) {
        $html .= '<div class="forms-field-error">' . forms_escape($error) . '</div>';
    }

    return $html . '</div>';
}

function forms_render_form($slugOrId, $errors, $values)
{
    global $_CONF, $LANG_FORMS;

    $form = forms_get_form($slugOrId);
    if (!$form || empty($form['is_active'])) {
        return COM_showMessageText($LANG_FORMS['inactive'], $LANG_FORMS['plugin_name']);
    }

    if (COM_isAnonUser() && empty($form['allow_anonymous'])) {
        return SEC_loginRequiredForm();
    }

    $fields = forms_get_fields($form['id']);
    $action = $_CONF['site_url'] . '/forms/index.php?f=' . rawurlencode($form['slug']);
    $html = '<div class="forms-wrapper">';
    $html .= '<style>.forms-wrapper{max-width:760px}.forms-field{margin:0 0 1rem}.forms-field label{display:block;font-weight:600;margin-bottom:.3rem}.forms-field input[type=text],.forms-field input[type=email],.forms-field input[type=date],.forms-field input[type=number],.forms-field input[type=tel],.forms-field input[type=url],.forms-field input[type=time],.forms-field input[type=datetime-local],.forms-field textarea,.forms-field select{box-sizing:border-box;width:100%;max-width:100%;padding:.55rem}.forms-choice{display:block;font-weight:400!important}.forms-label{font-weight:600;margin-bottom:.3rem}.forms-info{padding:.65rem 0}.forms-field-heading{margin-top:1.4rem}.forms-help{font-size:.9em;opacity:.75}.forms-required,.forms-field-error{color:#b00020}.forms-hp{position:absolute!important;left:-9999px!important;width:1px!important;height:1px!important;overflow:hidden!important}</style>';
    $html .= '<h2>' . forms_escape($form['title']) . '</h2>';
    if (!empty($form['description'])) {
        $html .= '<div class="forms-description">' . nl2br(forms_escape($form['description'])) . '</div>';
    }
    $hasRequired = false;
    foreach ($fields as $requiredField) {
        if (!empty($requiredField['is_required']) && $requiredField['type'] !== 'heading' && $requiredField['type'] !== 'info') {
            $hasRequired = true;
            break;
        }
    }
    if ($hasRequired) {
        $html .= '<p class="forms-required-note"><span class="forms-required">*</span> '
            . forms_escape($LANG_FORMS['required_fields_notice']) . '</p>';
    }
    if (!empty($errors['_form'])) {
        $html .= COM_showMessageText($errors['_form'], $LANG_FORMS['validation_error']);
    }
    $html .= '<form method="post" action="' . forms_escape($action) . '">';
    $html .= '<input type="hidden" name="form_id" value="' . (int) $form['id'] . '">';
    $html .= '<input type="hidden" name="form_started" value="' . time() . '">';
    $html .= '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . SEC_createToken() . '">';
    $html .= '<div class="forms-hp" aria-hidden="true"><label>Website<input type="text" name="website" value="" tabindex="-1" autocomplete="off"></label></div>';

    foreach ($fields as $field) {
        $value = isset($values[$field['name']]) ? $values[$field['name']] : '';
        $error = isset($errors[$field['name']]) ? $errors[$field['name']] : '';
        $html .= forms_render_field($field, $value, $error);
    }

    $html .= '<p><button type="submit" name="forms_submit" value="1">' . forms_escape($LANG_FORMS['submit']) . '</button></p>';
    $html .= '</form></div>';

    return $html;
}

function forms_validate_submission($form, $fields, $posted)
{
    global $LANG_FORMS;

    $errors = array();
    $values = array();

    foreach ($fields as $field) {
        $name = $field['name'];
        $type = strtolower($field['type']);

        if ($type === 'heading' || $type === 'info') {
            continue;
        }

        $rawValue = isset($posted[$name]) ? $posted[$name] : '';
        if ($type === 'checkboxes') {
            $value = is_array($rawValue) ? array_values(array_filter(array_map('trim', $rawValue), 'strlen')) : array();
            $empty = count($value) === 0;
        } else {
            if (is_array($rawValue)) {
                $rawValue = implode(', ', $rawValue);
            }
            $value = trim((string) $rawValue);
            $empty = ($value === '');
        }

        if (!empty($field['is_required']) && $empty) {
            $errors[$name] = $LANG_FORMS['required_field_error'];
        } elseif ($type === 'email' && !$empty && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$name] = $LANG_FORMS['invalid_email_error'];
        } elseif ($type === 'url' && !$empty && !filter_var($value, FILTER_VALIDATE_URL)) {
            $errors[$name] = $LANG_FORMS['invalid_url_error'];
        }

        $values[$name] = $value;
    }

    return array($errors, $values);
}

function forms_submission_value_to_string($value)
{
    if (is_array($value)) {
        return implode(', ', $value);
    }
    return (string) $value;
}

function forms_store_submission($form, $fields, $values)
{
    global $_TABLES, $_USER;

    $uid = isset($_USER['uid']) ? (int) $_USER['uid'] : 1;
    $ip = '';
    if (class_exists('Geeklog\\IP')) {
        $ip = Geeklog\IP::getIPAddress();
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $ipHash = hash('sha256', (string) $ip);
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '';

    DB_query("INSERT INTO {$_TABLES['forms_submissions']} (form_id,uid,submitted,ip_hash,user_agent) VALUES (" . (int) $form['id'] . "," . $uid . "," . time() . ",'" . DB_escapeString($ipHash) . "','" . DB_escapeString($ua) . "')");
    $submissionId = (int) DB_insertId();

    foreach ($fields as $field) {
        if ($field['type'] === 'heading' || $field['type'] === 'info') {
            continue;
        }
        $value = isset($values[$field['name']]) ? forms_submission_value_to_string($values[$field['name']]) : '';
        DB_query("INSERT INTO {$_TABLES['forms_submission_values']} (submission_id,field_id,field_name,field_value) VALUES (" . $submissionId . "," . (int) $field['id'] . ",'" . DB_escapeString($field['name']) . "','" . DB_escapeString($value) . "')");
    }

    return $submissionId;
}

function forms_mail_submission($form, $fields, $values)
{
    global $_CONF, $LANG_FORMS;

    $config = forms_get_config();
    $recipient = trim($form['recipient']);
    if ($recipient === '' && !empty($config['default_recipient'])) {
        $recipient = trim($config['default_recipient']);
    }
    if ($recipient === '') {
        $recipient = isset($_CONF['site_mail']) ? $_CONF['site_mail'] : '';
    }
    if ($recipient === '') {
        return false;
    }

    $message = $form['title'] . LB . str_repeat('-', 50) . LB;
    foreach ($fields as $field) {
        if ($field['type'] === 'heading' || $field['type'] === 'info') {
            continue;
        }
        $message .= $field['label'] . ': ' . (isset($values[$field['name']]) ? forms_submission_value_to_string($values[$field['name']]) : '') . LB;
    }

    $subject = sprintf($LANG_FORMS['mail_subject'], $form['title']);
    $fromEmail = isset($_CONF['site_mail']) ? $_CONF['site_mail'] : $recipient;
    $fromName = isset($_CONF['site_name']) ? $_CONF['site_name'] : 'Geeklog';
    $mailto = $recipient;
    $mailfrom = COM_formatEmailAddress($fromName, $fromEmail);

    return COM_mail($mailto, $subject, $message, $mailfrom);
}

function plugin_getadminoption_forms()
{
    global $_CONF, $LANG_FORMS;

    if (!SEC_hasRights('forms.admin')) {
        return array();
    }

    return array($LANG_FORMS['plugin_name'], $_CONF['site_admin_url'] . '/plugins/forms/index.php', '');
}

function plugin_getmenuitems_forms()
{
    // Forms is intentionally not a public catalogue. Individual forms are
    // exposed only through explicit URLs or future embedding mechanisms.
    return false;
}

function forms_normalize_options_input($options)
{
    $lines = preg_split('/\r\n|\r|\n/', (string) $options);
    $out = array();
    $used = array();

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $parts = explode('|', $line, 2);
        if (count($parts) === 2) {
            $value = trim($parts[0]);
            $label = trim($parts[1]);
        } else {
            $label = $line;
            $value = forms_slugify($label);
            if ($value === '') {
                $value = 'option';
            }
        }

        $base = $value;
        $suffix = 2;
        while (isset($used[$value])) {
            $value = $base . '-' . $suffix;
            $suffix++;
        }
        $used[$value] = true;
        $out[] = $value . '|' . $label;
    }

    return implode("\n", $out);
}

function plugin_autotags_forms($op, $content = '', $autotag = '')
{
    global $LANG_FORMS;

    if ($op == 'tagname') {
        return array('forms');
    } elseif ($op == 'description') {
        return array('forms' => $LANG_FORMS['autotag_description']);
    } elseif ($op == 'parse') {
        if (!is_array($autotag) || !isset($autotag['tag']) || $autotag['tag'] !== 'forms') {
            return $content;
        }

        $slug = isset($autotag['parm1']) ? forms_slugify($autotag['parm1']) : '';
        if ($slug === '') {
            return $content;
        }

        $form = forms_get_form($slug);
        if (!$form || empty($form['is_active'])) {
            return str_replace($autotag['tagstr'], '', $content);
        }

        $html = forms_render_form($slug, array(), array());
        return str_replace($autotag['tagstr'], $html, $content);
    }

    return $content;
}

function plugin_getversion_forms()
{
    return '0.3.2';
}

function plugin_chkVersion_forms()
{
    return '0.3.2';
}

function plugin_autouninstall_forms()
{
    return array(
        'tables' => array('forms_definitions','forms_fields','forms_submissions','forms_submission_values'),
        'groups' => array('Forms Admin'),
        'features' => array('forms.admin','config.forms.tab_main'),
        'php_blocks' => array(),
        'vars' => array()
    );
}


function plugin_upgrade_forms()
{
    global $_CONF, $_TABLES;

    require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once $_CONF['path'] . 'plugins/forms/install_defaults.php';

    if (!plugin_initconfig_forms()) {
        return false;
    }

    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '0.3.2', pi_gl_version = '2.1.1' WHERE pi_name = 'forms'", 1);
    if (DB_error()) {
        COM_errorLog('Forms Plugin: failed to update plugin metadata to 0.3.2.');
        return false;
    }

    return true;
}
