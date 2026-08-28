<?php
require_once '../../../lib-common.php';
if (!SEC_hasRights('forms.admin')) {
    COM_accessLog('User attempted to access Forms administration without permission.');
    COM_redirect($_CONF['site_admin_url'] . '/index.php');
}
function forms_admin_header($title)
{
    global $_CONF, $LANG_FORMS;
    return COM_startBlock(forms_escape($title))
        . '<p><a href="index.php">' . forms_escape($LANG_FORMS['plugin_name']) . '</a> | '
        . '<a href="index.php?mode=editform">' . forms_escape($LANG_FORMS['new_form']) . '</a> | '
        . '<a href="' . $_CONF['site_admin_url'] . '/configuration.php?tab-0=forms">'
        . forms_escape($LANG_FORMS['configuration']) . '</a></p>';
}
function forms_admin_footer() { return COM_endBlock(); }

function forms_admin_styles()
{
    return '<style>'
        . '.forms-admin-intro{background:#f7f9fb;border:1px solid #d8dee6;border-radius:4px;padding:16px 18px;margin:0 0 20px;}'
        . '.forms-admin-intro h2,.forms-admin-section h2{margin-top:0;}'
        . '.forms-admin-steps{margin:10px 0 0 20px;padding:0;}'
        . '.forms-admin-actions{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0 20px;}'
        . '.forms-admin-grid{display:flex;flex-wrap:wrap;gap:18px;align-items:flex-start;}'
        . '.forms-admin-main{flex:2 1 520px;min-width:280px;}'
        . '.forms-admin-side{flex:1 1 280px;min-width:250px;}'
        . '.forms-admin-section{border:1px solid #d8dee6;border-radius:4px;padding:16px;margin:0 0 18px;background:#fff;}'
        . '.forms-admin-section p:first-child{margin-top:0;}'
        . '.forms-admin-section label{font-weight:600;}'
        . '.forms-admin-section small{font-weight:normal;display:block;margin-top:4px;}'
        . '.forms-admin-section input[type=text],.forms-admin-section input[type=email],.forms-admin-section input[type=number],.forms-admin-section textarea,.forms-admin-section select{max-width:100%;box-sizing:border-box;}'
        . '.forms-admin-note{background:#f5fbff;border-left:4px solid #2d7091;padding:12px 14px;margin:0 0 18px;}'
        . '.forms-admin-note-warning{background:#fffbea;border-left-color:#e6a700;}'
        . '.forms-admin-code{display:block;background:#f5f5f5;border:1px solid #ddd;padding:8px 10px;margin:5px 0 10px;word-break:break-all;}'
        . '.forms-admin-status{display:inline-block;padding:2px 7px;border-radius:10px;background:#eee;font-size:.9em;}'
        . '.forms-admin-table{width:100%;border-collapse:collapse;}'
        . '.forms-admin-table th,.forms-admin-table td{padding:8px;vertical-align:top;border-bottom:1px solid #ddd;}'
        . '.forms-admin-table th{text-align:left;}'
        . '.forms-admin-muted{color:#666;}'
        . '.forms-admin-help-list{margin:8px 0 0 20px;}'
        . '.forms-admin-template-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;margin-top:10px;}'
        . '.forms-admin-template-card{border:1px solid #d8dee6;border-radius:4px;padding:12px;background:#f9fafb;}'
        . '.forms-admin-template-card h3{margin:0 0 6px;font-size:1.05em;}'
        . '.forms-admin-template-card p{margin:0 0 8px;line-height:1.45;}.forms-admin-template-button{display:inline-block!important;min-height:0!important;height:auto!important;padding:4px 10px!important;line-height:1.35!important;font-size:.9em!important;margin:0!important;}'
        . '.forms-admin-actions a.uk-button:hover,.forms-admin-actions a.uk-button:focus,.forms-admin-section a.uk-button:hover,.forms-admin-section a.uk-button:focus,.forms-admin-template-card a.uk-button:hover,.forms-admin-template-card a.uk-button:focus{color:#fff!important;text-decoration:none!important;}'
        . '.forms-admin-actions .uk-button-danger:hover,.forms-admin-actions .uk-button-danger:focus{color:#fff!important;}'
        . '</style>';
}

function forms_admin_getting_started()
{
    global $_CONF, $LANG_FORMS;

    $html = '<div class="forms-admin-intro">'
        . '<h2>' . forms_escape($LANG_FORMS['getting_started_title']) . '</h2>'
        . '<p>' . forms_escape($LANG_FORMS['getting_started_intro']) . '</p>'
        . '<ol class="forms-admin-steps">'
        . '<li>' . forms_escape($LANG_FORMS['getting_started_step1']) . '</li>'
        . '<li>' . forms_escape($LANG_FORMS['getting_started_step2']) . '</li>'
        . '<li>' . forms_escape($LANG_FORMS['getting_started_step3']) . '</li>'
        . '<li>' . forms_escape($LANG_FORMS['getting_started_step4']) . '</li>'
        . '<li>' . forms_escape($LANG_FORMS['getting_started_step5']) . '</li>'
        . '</ol>'
        . '<p><strong>' . forms_escape($LANG_FORMS['publish_title']) . '</strong><br>'
        . forms_escape($LANG_FORMS['publish_help']) . '</p>'
        . '<p><strong>' . forms_escape($LANG_FORMS['privacy_title']) . '</strong><br>'
        . forms_escape($LANG_FORMS['privacy_help']) . '</p>'
        . '<p><strong>' . forms_escape($LANG_FORMS['spam_title']) . '</strong><br>'
        . forms_escape($LANG_FORMS['spam_help']) . '</p>'
        . '</div>';

    return $html;
}
function forms_admin_field_type_label($type)
{
    global $LANG_FORMS;
    $key = 'field_type_' . $type;
    return isset($LANG_FORMS[$key]) ? $LANG_FORMS[$key] : $type;
}
function forms_admin_next_field_order($formId)
{
    global $_TABLES;
    $value = DB_getItem($_TABLES['forms_fields'], 'MAX(field_order)', 'form_id=' . (int) $formId);
    return max(10, (int) $value + 10);
}
function forms_admin_list()
{
    global $_TABLES, $_CONF, $LANG_FORMS;

    $html = forms_admin_header($LANG_FORMS['admin_title']);
    $html .= forms_admin_styles();
    $html .= '<div class="forms-admin-actions">'
        . '<a class="uk-button uk-button-primary" href="index.php?mode=editform">'
        . forms_escape($LANG_FORMS['new_form']) . '</a>'
        . '<a class="uk-button" href="' . $_CONF['site_admin_url'] . '/configuration.php?tab-0=forms">'
        . forms_escape($LANG_FORMS['configuration']) . '</a>'
        . '</div>';

    $templateToken = SEC_createToken();
    $html .= '<div class="forms-admin-section"><h2>' . forms_escape($LANG_FORMS['templates_title']) . '</h2>'
        . '<p>' . forms_escape($LANG_FORMS['templates_help']) . '</p>'
        . '<div class="forms-admin-template-grid">';

    $templates = array(
        'contact' => array('template_contact', 'template_contact_card_help'),
        'feedback' => array('template_feedback', 'template_feedback_card_help'),
        'event' => array('template_event', 'template_event_card_help'),
        'support' => array('template_support', 'template_support_card_help'),
        'quote' => array('template_quote', 'template_quote_card_help')
    );
    foreach ($templates as $templateKey => $templateInfo) {
        $html .= '<div class="forms-admin-template-card"><h3>'
            . forms_escape($LANG_FORMS[$templateInfo[0]]) . '</h3><p>'
            . forms_escape($LANG_FORMS[$templateInfo[1]]) . '</p>'
            . '<a class="uk-button forms-admin-template-button" href="index.php?mode=createtemplate&amp;template=' . rawurlencode($templateKey)
            . '&amp;' . CSRF_TOKEN . '=' . $templateToken . '">'
            . forms_escape($LANG_FORMS['create_template']) . '</a></div>';
    }
    $html .= '</div></div>';

    $result = DB_query("SELECT f.*, (SELECT COUNT(*) FROM {$_TABLES['forms_fields']} ff WHERE ff.form_id=f.id) AS field_count, "
        . "(SELECT COUNT(*) FROM {$_TABLES['forms_submissions']} s WHERE s.form_id=f.id) AS submission_count "
        . "FROM {$_TABLES['forms_definitions']} f ORDER BY title", 1);

    $html .= '<div class="forms-admin-section"><h2>' . forms_escape($LANG_FORMS['forms_list_title']) . '</h2>';
    if (DB_numRows($result) < 1) {
        $html .= '<p>' . forms_escape($LANG_FORMS['no_forms_yet']) . '</p></div>';
        $html .= forms_admin_getting_started();
        return $html . forms_admin_footer();
    }

    $html .= '<table class="admin-list forms-admin-table"><thead><tr>'
        . '<th>' . forms_escape($LANG_FORMS['title']) . '</th><th>Slug</th>'
        . '<th>' . forms_escape($LANG_FORMS['fields']) . '</th>'
        . '<th>' . forms_escape($LANG_FORMS['submissions_column']) . '</th>'
        . '<th>' . forms_escape($LANG_FORMS['active']) . '</th><th>' . forms_escape($LANG_FORMS['actions']) . '</th>'
        . '</tr></thead><tbody>';

    while ($row = DB_fetchArray($result)) {
        $id = (int) $row['id'];
        $html .= '<tr><td><strong><a href="index.php?mode=editform&id=' . $id . '">'
            . forms_escape($row['title']) . '</a></strong></td>';
        $html .= '<td><code>' . forms_escape($row['slug']) . '</code><br><a class="forms-admin-muted" href="'
            . $_CONF['site_url'] . '/forms/index.php?f=' . rawurlencode($row['slug']) . '">'
            . forms_escape($LANG_FORMS['view_form']) . '</a></td>';
        $html .= '<td><a href="index.php?mode=editform&id=' . $id . '#forms-fields">'
            . (int) $row['field_count'] . '</a></td>';
        if (!empty($row['store_results'])) {
            $html .= '<td><a title="' . forms_escape($LANG_FORMS['stored_submissions_help'])
                . '" href="index.php?mode=submissions&form_id=' . $id . '">' . (int) $row['submission_count'] . '</a></td>';
        } else {
            $html .= '<td title="' . forms_escape($LANG_FORMS['stored_submissions_disabled']) . '">—</td>';
        }
        $html .= '<td><span class="forms-admin-status">'
            . (!empty($row['is_active']) ? forms_escape($LANG_FORMS['yes']) : forms_escape($LANG_FORMS['no']))
            . '</span></td>';
        $html .= '<td><a href="index.php?mode=editform&id=' . $id . '">' . forms_escape($LANG_FORMS['edit_form']) . '</a> | '
            . '<a href="index.php?mode=duplicateform&id=' . $id . '&amp;' . CSRF_TOKEN . '=' . SEC_createToken() . '">'
            . forms_escape($LANG_FORMS['duplicate']) . '</a> | '
            . '<a href="index.php?mode=deleteform&id=' . $id . '&amp;' . CSRF_TOKEN . '=' . SEC_createToken()
            . '" onclick="return confirm(\'' . forms_escape($LANG_FORMS['confirm_delete']) . '\')">'
            . forms_escape($LANG_FORMS['delete']) . '</a></td></tr>';
    }

    $html .= '</tbody></table></div>';
    $html .= forms_admin_getting_started();
    return $html . forms_admin_footer();
}
function forms_admin_form_editor($id)
{
    global $_TABLES, $_CONF, $LANG_FORMS;

    $config = forms_get_config();
    $defaultRecipient = !empty($config['default_recipient']) ? trim($config['default_recipient']) : '';
    $form = array(
        'id'=>0, 'slug'=>'', 'title'=>'', 'description'=>'', 'success_message'=>'',
        'is_active'=>1, 'allow_anonymous'=>1, 'store_results'=>1, 'email_results'=>0,
        'recipient'=>$defaultRecipient
    );
    if ($id > 0) {
        $loaded = forms_get_form($id);
        if ($loaded) {
            $form = $loaded;
        }
    }

    $html = forms_admin_header($id ? $LANG_FORMS['edit_form'] : $LANG_FORMS['new_form']);
    $html .= forms_admin_styles();

    if ($id < 1) {
        $html .= '<div class="forms-admin-note forms-admin-note-warning"><strong>'
            . forms_escape($LANG_FORMS['new_form_notice_title']) . '</strong><br>'
            . forms_escape($LANG_FORMS['save_before_fields']) . '</div>';
    }

    if ($id > 0) {
        $publicUrl = $_CONF['site_url'] . '/forms/index.php?f=' . rawurlencode($form['slug']);
        $html .= '<div class="forms-admin-section"><h2>' . forms_escape($LANG_FORMS['use_form_title']) . '</h2>'
            . '<p>' . forms_escape($LANG_FORMS['use_form_help']) . '</p>'
            . '<strong>' . forms_escape($LANG_FORMS['public_url']) . '</strong>'
            . '<a class="forms-admin-code" href="' . forms_escape($publicUrl) . '">' . forms_escape($publicUrl) . '</a>'
            . '<strong>' . forms_escape($LANG_FORMS['autotag']) . '</strong>'
            . '<code class="forms-admin-code">[forms:' . forms_escape($form['slug']) . ']</code>'
            . '<div class="forms-admin-actions">'
            . '<a class="uk-button" href="' . forms_escape($publicUrl) . '">' . forms_escape($LANG_FORMS['preview_form']) . '</a>'
            . '<a class="uk-button" href="#forms-fields">' . forms_escape($LANG_FORMS['manage_fields']) . '</a>';
        if (!empty($form['store_results'])) {
            $html .= '<a class="uk-button" href="#forms-submissions">' . forms_escape($LANG_FORMS['stored_submissions_title']) . '</a>';
        }
        $html .= '</div></div>';
    }

    $html .= '<form method="post" action="index.php">'
        . '<input type="hidden" name="mode" value="saveform">'
        . '<input type="hidden" name="id" value="' . (int) $form['id'] . '">'
        . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . SEC_createToken() . '">';

    $html .= '<div class="forms-admin-grid"><div class="forms-admin-main">';
    $html .= '<div class="forms-admin-section"><h2>' . forms_escape($LANG_FORMS['identity_section']) . '</h2>'
        . '<p class="forms-admin-muted">' . forms_escape($LANG_FORMS['identity_help']) . '</p>'
        . '<p><label>' . forms_escape($LANG_FORMS['title']) . '<br>'
        . '<input type="text" name="title" value="' . forms_escape($form['title']) . '" size="70" required></label>'
        . '<small>' . forms_escape($LANG_FORMS['title_help']) . '</small></p>'
        . '<p><label>Slug<br><input type="text" name="slug" value="' . forms_escape($form['slug']) . '" size="50"></label>'
        . '<small>' . forms_escape($LANG_FORMS['slug_help']) . '</small></p>'
        . '<p><label>' . forms_escape($LANG_FORMS['description']) . '<br>'
        . '<textarea name="description" rows="5" cols="80">' . forms_escape($form['description']) . '</textarea></label>'
        . '<small>' . forms_escape($LANG_FORMS['description_help']) . '</small></p>'
        . '<p><label>' . forms_escape($LANG_FORMS['success_message']) . '<br>'
        . '<textarea name="success_message" rows="3" cols="80">' . forms_escape($form['success_message']) . '</textarea></label>'
        . '<small>' . forms_escape($LANG_FORMS['success_help']) . '</small></p></div>';

    $html .= '<div class="forms-admin-section"><h2>' . forms_escape($LANG_FORMS['behavior_section']) . '</h2>'
        . '<p class="forms-admin-muted">' . forms_escape($LANG_FORMS['behavior_help']) . '</p>'
        . '<p><label><input type="checkbox" name="is_active" value="1"'
        . (!empty($form['is_active']) ? ' checked' : '') . '> ' . forms_escape($LANG_FORMS['active']) . '</label>'
        . '<small>' . forms_escape($LANG_FORMS['active_help']) . '</small></p>'
        . '<p><label><input type="checkbox" name="allow_anonymous" value="1"'
        . (!empty($form['allow_anonymous']) ? ' checked' : '') . '> ' . forms_escape($LANG_FORMS['allow_anonymous']) . '</label>'
        . '<small>' . forms_escape($LANG_FORMS['anonymous_help']) . '</small></p>'
        . '<p><label><input type="checkbox" name="store_results" value="1"'
        . (!empty($form['store_results']) ? ' checked' : '') . '> ' . forms_escape($LANG_FORMS['store_results']) . '</label>'
        . '<small>' . forms_escape($LANG_FORMS['store_results_help']) . '</small></p>'
        . '<p><label><input type="checkbox" name="email_results" value="1"'
        . (!empty($form['email_results']) ? ' checked' : '') . '> ' . forms_escape($LANG_FORMS['email_results']) . '</label>'
        . '<small>' . forms_escape($LANG_FORMS['email_results_help']) . '</small></p>'
        . '<p><label>' . forms_escape($LANG_FORMS['recipient_email']) . '<br>'
        . '<input type="email" name="recipient" value="' . forms_escape($form['recipient']) . '" size="70"></label>';
    if ($defaultRecipient !== '') {
        $html .= '<small>' . forms_escape($LANG_FORMS['default_recipient_hint']) . ': ' . forms_escape($defaultRecipient) . '</small>';
    }
    $html .= '</p></div></div>';

    $html .= '<div class="forms-admin-side"><div class="forms-admin-section"><h2>'
        . forms_escape($LANG_FORMS['editor_help_title']) . '</h2><ul class="forms-admin-help-list">'
        . '<li>' . forms_escape($LANG_FORMS['editor_help_1']) . '</li>'
        . '<li>' . forms_escape($LANG_FORMS['editor_help_2']) . '</li>'
        . '<li>' . forms_escape($LANG_FORMS['editor_help_3']) . '</li>'
        . '<li>' . forms_escape($LANG_FORMS['editor_help_4']) . '</li>'
        . '</ul></div>';
    if ($id > 0) {
        $html .= '<div class="forms-admin-section"><h2>' . forms_escape($LANG_FORMS['form_status_title']) . '</h2>'
            . '<p><strong>' . forms_escape($LANG_FORMS['active']) . ':</strong> '
            . (!empty($form['is_active']) ? forms_escape($LANG_FORMS['yes']) : forms_escape($LANG_FORMS['no'])) . '</p>'
            . '<p><strong>' . forms_escape($LANG_FORMS['fields']) . ':</strong> '
            . (int) DB_count($_TABLES['forms_fields'], 'form_id', (int) $id) . '</p>'
            . '<p><strong>' . forms_escape($LANG_FORMS['stored_submissions_title']) . ':</strong> '
            . (int) DB_count($_TABLES['forms_submissions'], 'form_id', (int) $id) . '</p></div>';
    }
    $html .= '</div></div>';

    $html .= '<div class="forms-admin-actions"><button class="uk-button uk-button-primary" type="submit">'
        . forms_escape($LANG_FORMS['save']) . '</button> <a class="uk-button" href="index.php">'
        . forms_escape($LANG_FORMS['cancel']) . '</a></div></form>';

    if ($id > 0) {
        $html .= '<div class="forms-admin-section" id="forms-fields"><h2>' . forms_escape($LANG_FORMS['fields_section_title']) . '</h2>'
            . '<p class="forms-admin-muted">' . forms_escape($LANG_FORMS['fields_section_help']) . '</p>';

        $res = DB_query("SELECT * FROM {$_TABLES['forms_fields']} WHERE form_id=" . (int) $id . " ORDER BY field_order,id");
        $rows = array();
        while ($r = DB_fetchArray($res)) {
            $rows[] = $r;
        }
        if (count($rows) > 0) {
            $html .= '<table class="forms-admin-table"><thead><tr><th>' . forms_escape($LANG_FORMS['order']) . '</th>'
                . '<th>' . forms_escape($LANG_FORMS['label']) . '</th><th>' . forms_escape($LANG_FORMS['name']) . '</th>'
                . '<th>' . forms_escape($LANG_FORMS['type']) . '</th><th>' . forms_escape($LANG_FORMS['required']) . '</th>'
                . '<th>' . forms_escape($LANG_FORMS['actions']) . '</th></tr></thead><tbody>';
            $n = count($rows);
            for ($i = 0; $i < $n; $i++) {
                $r = $rows[$i];
                $fid = (int) $r['id'];
                $html .= '<tr><td>' . (int) $r['field_order'] . '</td><td><strong><a href="index.php?mode=editform&id='
                    . (int) $id . '&field_id=' . $fid . '#forms-field-editor">' . forms_escape($r['label']) . '</a></strong></td>'
                    . '<td><code>' . forms_escape($r['name']) . '</code></td><td>'
                    . forms_escape(forms_admin_field_type_label($r['type'])) . '</td><td>'
                    . (!empty($r['is_required']) ? forms_escape($LANG_FORMS['yes']) : forms_escape($LANG_FORMS['no'])) . '</td><td>';
                if ($i > 0) {
                    $html .= '<a title="' . forms_escape($LANG_FORMS['move_up']) . '" href="index.php?mode=movefield&id=' . $fid
                        . '&form_id=' . (int) $id . '&direction=up&amp;' . CSRF_TOKEN . '=' . SEC_createToken() . '">↑</a> ';
                }
                if ($i < $n - 1) {
                    $html .= '<a title="' . forms_escape($LANG_FORMS['move_down']) . '" href="index.php?mode=movefield&id=' . $fid
                        . '&form_id=' . (int) $id . '&direction=down&amp;' . CSRF_TOKEN . '=' . SEC_createToken() . '">↓</a> ';
                }
                $html .= '<a href="index.php?mode=duplicatefield&id=' . $fid . '&form_id=' . (int) $id . '&amp;'
                    . CSRF_TOKEN . '=' . SEC_createToken() . '">' . forms_escape($LANG_FORMS['duplicate']) . '</a> | '
                    . '<a href="index.php?mode=deletefield&id=' . $fid . '&form_id=' . (int) $id . '&amp;'
                    . CSRF_TOKEN . '=' . SEC_createToken() . '" onclick="return confirm(\''
                    . forms_escape($LANG_FORMS['confirm_delete']) . '\')">' . forms_escape($LANG_FORMS['delete']) . '</a></td></tr>';
            }
            $html .= '</tbody></table>';
        } else {
            $html .= '<div class="forms-admin-note">' . forms_escape($LANG_FORMS['no_fields']) . '</div>';
        }

        $fieldId = isset($_GET['field_id']) ? (int) $_GET['field_id'] : 0;
        $field = array('id'=>0,'form_id'=>$id,'field_order'=>forms_admin_next_field_order($id),'name'=>'','label'=>'','type'=>'text','options'=>'','placeholder'=>'','help_text'=>'','is_required'=>0,'is_active'=>1);
        if ($fieldId > 0) {
            $rr = DB_query("SELECT * FROM {$_TABLES['forms_fields']} WHERE id=$fieldId AND form_id=" . (int) $id . " LIMIT 1");
            if (DB_numRows($rr)) {
                $field = DB_fetchArray($rr);
            }
        }

        $html .= '<div id="forms-field-editor" style="margin-top:22px"><h3>'
            . forms_escape($fieldId > 0 ? $LANG_FORMS['edit_field'] : $LANG_FORMS['add_field']) . '</h3>'
            . '<p class="forms-admin-muted">' . forms_escape($LANG_FORMS['field_editor_intro']) . '</p>'
            . '<form method="post" action="index.php"><input type="hidden" name="mode" value="savefield">'
            . '<input type="hidden" name="id" value="' . (int) $field['id'] . '">'
            . '<input type="hidden" name="form_id" value="' . (int) $id . '">'
            . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . SEC_createToken() . '">'
            . '<div class="forms-admin-grid"><div class="forms-admin-main">'
            . '<p><label>' . forms_escape($LANG_FORMS['label']) . '<br><input type="text" name="label" value="'
            . forms_escape($field['label']) . '" size="70" required></label><small>' . forms_escape($LANG_FORMS['label_help']) . '</small></p>'
            . '<p><label>' . forms_escape($LANG_FORMS['name']) . '<br><input type="text" name="name" value="'
            . forms_escape($field['name']) . '" size="50"></label><small>' . forms_escape($LANG_FORMS['name_help']) . '</small></p>'
            . '<p><label>' . forms_escape($LANG_FORMS['type']) . '<br><select name="type">';
        foreach (array('text','email','textarea','select','radio','checkbox','checkboxes','phone','url','date','time','datetime','number','consent','heading','info') as $t) {
            $html .= '<option value="' . $t . '"' . ($field['type'] === $t ? ' selected' : '') . '>'
                . forms_escape(forms_admin_field_type_label($t)) . '</option>';
        }
        $html .= '</select></label><small>' . forms_escape($LANG_FORMS['type_help']) . '</small></p>'
            . '<p><label>' . forms_escape($LANG_FORMS['options']) . '<br><textarea name="options" rows="6" cols="70">'
            . forms_escape($field['options']) . '</textarea></label><small>' . forms_escape($LANG_FORMS['options_help']) . '</small></p>'
            . '</div><div class="forms-admin-side">'
            . '<p><label>' . forms_escape($LANG_FORMS['placeholder']) . '<br><input type="text" name="placeholder" value="'
            . forms_escape($field['placeholder']) . '" size="45"></label><small>' . forms_escape($LANG_FORMS['placeholder_help']) . '</small></p>'
            . '<p><label>' . forms_escape($LANG_FORMS['help_text']) . '<br><input type="text" name="help_text" value="'
            . forms_escape($field['help_text']) . '" size="45"></label><small>' . forms_escape($LANG_FORMS['help_text_help']) . '</small></p>'
            . '<p><label>' . forms_escape($LANG_FORMS['order']) . '<br><input type="number" name="field_order" value="'
            . (int) $field['field_order'] . '"></label><small>' . forms_escape($LANG_FORMS['order_help']) . '</small></p>'
            . '<p><label><input type="checkbox" name="is_required" value="1"'
            . (!empty($field['is_required']) ? ' checked' : '') . '> ' . forms_escape($LANG_FORMS['required']) . '</label></p>'
            . '<p><label><input type="checkbox" name="is_active" value="1"'
            . (!empty($field['is_active']) ? ' checked' : '') . '> ' . forms_escape($LANG_FORMS['active']) . '</label></p>'
            . '</div></div><div class="forms-admin-actions"><button class="uk-button uk-button-primary" type="submit">'
            . forms_escape($LANG_FORMS['save_field']) . '</button>';
        if ($fieldId > 0) {
            $html .= '<a class="uk-button" href="index.php?mode=editform&id=' . (int) $id . '#forms-fields">'
                . forms_escape($LANG_FORMS['cancel']) . '</a>';
        }
        $html .= '</div></form></div></div>';

        $cnt = (int) DB_count($_TABLES['forms_submissions'], 'form_id', (int) $id);
        $html .= '<div class="forms-admin-section" id="forms-submissions"><h2>'
            . forms_escape($LANG_FORMS['stored_submissions_title']) . '</h2>';
        if (!empty($form['store_results'])) {
            $html .= '<p>' . forms_escape($LANG_FORMS['stored_submissions_help']) . '</p><div class="forms-admin-actions">'
                . '<a class="uk-button" href="index.php?mode=submissions&form_id=' . (int) $id . '">'
                . forms_escape(sprintf($LANG_FORMS['view_submissions'], $cnt)) . '</a>'
                . '<a class="uk-button" href="index.php?mode=exportcsv&form_id=' . (int) $id . '">'
                . forms_escape($LANG_FORMS['export_csv']) . '</a></div>';
        } else {
            $html .= '<p>' . forms_escape($LANG_FORMS['stored_submissions_disabled']) . '</p>';
        }
        $html .= '</div>';
    }

    return $html . forms_admin_footer();
}
function forms_admin_save_form()
{
    global $_TABLES,$_USER; if(!SEC_checkToken())return false; $id=isset($_POST['id'])?(int)$_POST['id']:0; $title=trim(isset($_POST['title'])?$_POST['title']:''); $slug=forms_slugify(isset($_POST['slug'])&&trim($_POST['slug'])!==''?$_POST['slug']:$title); if($title===''||$slug==='')return false;
    $data=array('title'=>$title,'slug'=>$slug,'description'=>isset($_POST['description'])?$_POST['description']:'','success_message'=>isset($_POST['success_message'])?$_POST['success_message']:'','is_active'=>isset($_POST['is_active'])?1:0,'allow_anonymous'=>isset($_POST['allow_anonymous'])?1:0,'store_results'=>isset($_POST['store_results'])?1:0,'email_results'=>isset($_POST['email_results'])?1:0,'recipient'=>trim(isset($_POST['recipient'])?$_POST['recipient']:''));
    if($id>0){DB_query("UPDATE {$_TABLES['forms_definitions']} SET title='".DB_escapeString($data['title'])."',slug='".DB_escapeString($data['slug'])."',description='".DB_escapeString($data['description'])."',success_message='".DB_escapeString($data['success_message'])."',is_active=".$data['is_active'].",allow_anonymous=".$data['allow_anonymous'].",store_results=".$data['store_results'].",email_results=".$data['email_results'].",recipient='".DB_escapeString($data['recipient'])."',modified=".time()." WHERE id=$id"); return $id;}
    DB_query("INSERT INTO {$_TABLES['forms_definitions']} (slug,title,description,success_message,is_active,allow_anonymous,store_results,email_results,recipient,created,modified,owner_id) VALUES ('".DB_escapeString($data['slug'])."','".DB_escapeString($data['title'])."','".DB_escapeString($data['description'])."','".DB_escapeString($data['success_message'])."',".$data['is_active'].",".$data['allow_anonymous'].",".$data['store_results'].",".$data['email_results'].",'".DB_escapeString($data['recipient'])."',".time().",".time().",".(int)$_USER['uid'].")"); return (int)DB_insertId();
}
function forms_admin_save_field()
{
    global $_TABLES; if(!SEC_checkToken())return false; $id=isset($_POST['id'])?(int)$_POST['id']:0; $formId=isset($_POST['form_id'])?(int)$_POST['form_id']:0; $label=trim(isset($_POST['label'])?$_POST['label']:''); $name=forms_slugify(isset($_POST['name'])&&trim($_POST['name'])!==''?$_POST['name']:$label); $name=str_replace('-','_',$name); $type=isset($_POST['type'])?$_POST['type']:'text'; $allowed=array('text','email','textarea','select','radio','checkbox','checkboxes','phone','url','date','time','datetime','number','consent','heading','info'); if(!in_array($type,$allowed,true))$type='text'; if($formId<1||$label===''||$name==='')return false; $order=isset($_POST['field_order'])?(int)$_POST['field_order']:0; if($order<1)$order=forms_admin_next_field_order($formId); $options=isset($_POST['options'])?forms_normalize_options_input($_POST['options']):'';
    $required = isset($_POST['is_required']) ? 1 : 0;
    if ($type === 'consent') $required = 1;
    if ($type === 'heading' || $type === 'info') $required = 0;
    $sets="form_id=$formId,field_order=$order,name='".DB_escapeString($name)."',label='".DB_escapeString($label)."',type='".DB_escapeString($type)."',options='".DB_escapeString($options)."',placeholder='".DB_escapeString(isset($_POST['placeholder'])?$_POST['placeholder']:'')."',help_text='".DB_escapeString(isset($_POST['help_text'])?$_POST['help_text']:'')."',is_required=".$required.",is_active=".(isset($_POST['is_active'])?1:0); if($id>0){DB_query("UPDATE {$_TABLES['forms_fields']} SET $sets WHERE id=$id AND form_id=$formId");return $formId;} DB_query("INSERT INTO {$_TABLES['forms_fields']} SET $sets"); return $formId;
}
function forms_admin_unique_slug($base)
{
    global $_TABLES; $base=forms_slugify($base); if($base==='')$base='form'; $slug=$base; $i=2; while(DB_count($_TABLES['forms_definitions'],'slug',$slug)>0){$slug=substr($base,0,56).'-'.$i;$i++;} return $slug;
}
function forms_admin_duplicate_form($id)
{
    global $_TABLES,$_USER,$LANG_FORMS; $form=forms_get_form($id); if(!$form)return 0; $slug=forms_admin_unique_slug($form['slug'].'-copy'); $title=$form['title'].' '.$LANG_FORMS['copy_suffix']; $now=time(); DB_query("INSERT INTO {$_TABLES['forms_definitions']} (slug,title,description,success_message,is_active,allow_anonymous,store_results,email_results,recipient,created,modified,owner_id) VALUES ('".DB_escapeString($slug)."','".DB_escapeString($title)."','".DB_escapeString($form['description'])."','".DB_escapeString($form['success_message'])."',0,".(int)$form['allow_anonymous'].",".(int)$form['store_results'].",".(int)$form['email_results'].",'".DB_escapeString($form['recipient'])."',$now,$now,".(int)$_USER['uid'].")"); $newId=(int)DB_insertId(); if($newId>0){$fields=DB_query("SELECT * FROM {$_TABLES['forms_fields']} WHERE form_id=".(int)$id." ORDER BY field_order,id"); while($f=DB_fetchArray($fields))DB_query("INSERT INTO {$_TABLES['forms_fields']} (form_id,field_order,name,label,type,options,placeholder,help_text,is_required,is_active) VALUES ($newId,".(int)$f['field_order'].",'".DB_escapeString($f['name'])."','".DB_escapeString($f['label'])."','".DB_escapeString($f['type'])."','".DB_escapeString($f['options'])."','".DB_escapeString($f['placeholder'])."','".DB_escapeString($f['help_text'])."',".(int)$f['is_required'].",".(int)$f['is_active'].")");} return $newId;
}
function forms_admin_duplicate_field($formId,$id)
{
    global $_TABLES,$LANG_FORMS; $r=DB_query("SELECT * FROM {$_TABLES['forms_fields']} WHERE id=".(int)$id." AND form_id=".(int)$formId." LIMIT 1"); if(DB_numRows($r)<1)return false; $f=DB_fetchArray($r); $base=substr($f['name'],0,52).'_copy'; $name=$base;$i=2;while(DB_count($_TABLES['forms_fields'],array('form_id','name'),array($formId,$name))>0){$name=substr($base,0,56).'_'.$i;$i++;} DB_query("INSERT INTO {$_TABLES['forms_fields']} (form_id,field_order,name,label,type,options,placeholder,help_text,is_required,is_active) VALUES (".(int)$formId.",".forms_admin_next_field_order($formId).",'".DB_escapeString($name)."','".DB_escapeString($f['label'].' '.$LANG_FORMS['copy_suffix'])."','".DB_escapeString($f['type'])."','".DB_escapeString($f['options'])."','".DB_escapeString($f['placeholder'])."','".DB_escapeString($f['help_text'])."',".(int)$f['is_required'].",".(int)$f['is_active'].")"); return true;
}
function forms_admin_move_field($formId,$id,$direction)
{
    global $_TABLES; $r=DB_query("SELECT id,field_order FROM {$_TABLES['forms_fields']} WHERE form_id=".(int)$formId." ORDER BY field_order,id"); $rows=array();while($x=DB_fetchArray($r))$rows[]=$x;$idx=-1;for($i=0;$i<count($rows);$i++)if((int)$rows[$i]['id']===(int)$id){$idx=$i;break;}if($idx<0)return false;$other=$direction==='up'?$idx-1:$idx+1;if($other<0||$other>=count($rows))return false;$a=(int)$rows[$idx]['field_order'];$b=(int)$rows[$other]['field_order'];if($a===$b){$a=($idx+1)*10;$b=($other+1)*10;}DB_query("UPDATE {$_TABLES['forms_fields']} SET field_order=$b WHERE id=".(int)$rows[$idx]['id']);DB_query("UPDATE {$_TABLES['forms_fields']} SET field_order=$a WHERE id=".(int)$rows[$other]['id']);return true;
}
function forms_admin_create_template($template)
{
    global $_TABLES, $_USER, $LANG_FORMS;

    $config = forms_get_config();
    $recipient = !empty($config['default_recipient']) ? trim($config['default_recipient']) : '';
    $now = time();

    switch ($template) {
        case 'feedback':
            $title = $LANG_FORMS['template_feedback'];
            $slug = forms_admin_unique_slug('feedback');
            $description = $LANG_FORMS['template_feedback_description'];
            $fields = array(
                array(10, 'name', $LANG_FORMS['preset_name'], 'text', '', 1, ''),
                array(20, 'email', $LANG_FORMS['preset_email'], 'email', '', 1, ''),
                array(30, 'rating', $LANG_FORMS['preset_rating'], 'select',
                    "excellent|" . $LANG_FORMS['rating_excellent'] . "\ngood|" . $LANG_FORMS['rating_good']
                    . "\naverage|" . $LANG_FORMS['rating_average'] . "\npoor|" . $LANG_FORMS['rating_poor'], 0, ''),
                array(40, 'message', $LANG_FORMS['preset_feedback'], 'textarea', '', 1, '')
            );
            break;

        case 'event':
            $title = $LANG_FORMS['template_event'];
            $slug = forms_admin_unique_slug('event-registration');
            $description = $LANG_FORMS['template_event_description'];
            $fields = array(
                array(10, 'intro', $LANG_FORMS['preset_event_details'], 'heading', '', 0, ''),
                array(20, 'event_info', $LANG_FORMS['preset_event_info'], 'info', '', 0, $LANG_FORMS['preset_event_info_help']),
                array(30, 'name', $LANG_FORMS['preset_name'], 'text', '', 1, ''),
                array(40, 'email', $LANG_FORMS['preset_email'], 'email', '', 1, ''),
                array(50, 'phone', $LANG_FORMS['preset_phone'], 'tel', '', 0, ''),
                array(60, 'preferred_time', $LANG_FORMS['preset_preferred_datetime'], 'datetime', '', 0, ''),
                array(70, 'interests', $LANG_FORMS['preset_interests'], 'checkboxes',
                    "conference|" . $LANG_FORMS['interest_conference'] . "\nworkshop|" . $LANG_FORMS['interest_workshop']
                    . "\nnetworking|" . $LANG_FORMS['interest_networking'], 0, ''),
                array(80, 'consent', $LANG_FORMS['preset_consent'], 'consent', '', 1, '')
            );
            break;

        case 'support':
            $title = $LANG_FORMS['template_support'];
            $slug = forms_admin_unique_slug('support-request');
            $description = $LANG_FORMS['template_support_description'];
            $fields = array(
                array(10, 'name', $LANG_FORMS['preset_name'], 'text', '', 1, ''),
                array(20, 'email', $LANG_FORMS['preset_email'], 'email', '', 1, ''),
                array(30, 'website', $LANG_FORMS['preset_website'], 'url', '', 0, ''),
                array(40, 'topic', $LANG_FORMS['preset_support_topic'], 'select',
                    "technical|" . $LANG_FORMS['support_technical'] . "\naccount|" . $LANG_FORMS['support_account']
                    . "\nbilling|" . $LANG_FORMS['support_billing'] . "\nother|" . $LANG_FORMS['support_other'], 1, ''),
                array(50, 'subject', $LANG_FORMS['preset_subject'], 'text', '', 1, ''),
                array(60, 'message', $LANG_FORMS['preset_message'], 'textarea', '', 1, '')
            );
            break;

        case 'quote':
            $title = $LANG_FORMS['template_quote'];
            $slug = forms_admin_unique_slug('quote-request');
            $description = $LANG_FORMS['template_quote_description'];
            $fields = array(
                array(10, 'contact_heading', $LANG_FORMS['preset_contact_details'], 'heading', '', 0, ''),
                array(20, 'name', $LANG_FORMS['preset_name'], 'text', '', 1, ''),
                array(30, 'email', $LANG_FORMS['preset_email'], 'email', '', 1, ''),
                array(40, 'phone', $LANG_FORMS['preset_phone'], 'tel', '', 0, ''),
                array(50, 'website', $LANG_FORMS['preset_website'], 'url', '', 0, ''),
                array(60, 'project_heading', $LANG_FORMS['preset_project_details'], 'heading', '', 0, ''),
                array(70, 'services', $LANG_FORMS['preset_services'], 'checkboxes',
                    "consulting|" . $LANG_FORMS['service_consulting'] . "\ntraining|" . $LANG_FORMS['service_training']
                    . "\ndevelopment|" . $LANG_FORMS['service_development'] . "\nother|" . $LANG_FORMS['service_other'], 1, ''),
                array(80, 'deadline', $LANG_FORMS['preset_deadline'], 'date', '', 0, ''),
                array(90, 'message', $LANG_FORMS['preset_project_description'], 'textarea', '', 1, ''),
                array(100, 'consent', $LANG_FORMS['preset_consent'], 'consent', '', 1, '')
            );
            break;

        case 'contact':
        default:
            $title = $LANG_FORMS['template_contact'];
            $slug = forms_admin_unique_slug('contact');
            $description = $LANG_FORMS['template_contact_description'];
            $fields = array(
                array(10, 'name', $LANG_FORMS['preset_name'], 'text', '', 1, ''),
                array(20, 'email', $LANG_FORMS['preset_email'], 'email', '', 1, ''),
                array(30, 'phone', $LANG_FORMS['preset_phone'], 'tel', '', 0, ''),
                array(40, 'subject', $LANG_FORMS['preset_subject'], 'text', '', 1, ''),
                array(50, 'message', $LANG_FORMS['preset_message'], 'textarea', '', 1, '')
            );
            break;
    }

    DB_query("INSERT INTO {$_TABLES['forms_definitions']} "
        . "(slug,title,description,success_message,is_active,allow_anonymous,store_results,email_results,recipient,created,modified,owner_id) VALUES ("
        . "'" . DB_escapeString($slug) . "','" . DB_escapeString($title) . "','" . DB_escapeString($description) . "',"
        . "'" . DB_escapeString($LANG_FORMS['default_success']) . "',1,1,1,1,'" . DB_escapeString($recipient) . "',"
        . $now . "," . $now . "," . (int) $_USER['uid'] . ")");
    $formId = (int) DB_insertId();

    foreach ($fields as $field) {
        DB_query("INSERT INTO {$_TABLES['forms_fields']} "
            . "(form_id,field_order,name,label,type,options,placeholder,help_text,is_required,is_active) VALUES ("
            . $formId . "," . (int) $field[0] . ",'" . DB_escapeString($field[1]) . "','"
            . DB_escapeString($field[2]) . "','" . DB_escapeString($field[3]) . "','"
            . DB_escapeString($field[4]) . "','','" . DB_escapeString($field[6]) . "',"
            . (int) $field[5] . ",1)");
    }

    return $formId;
}

function forms_admin_user_label($uid)
{
    global $_TABLES, $LANG_FORMS;

    $uid = (int) $uid;
    if ($uid <= 1) {
        return $LANG_FORMS['anonymous'];
    }

    $username = DB_getItem($_TABLES['users'], 'username', 'uid=' . $uid);
    if ($username === '') {
        return $LANG_FORMS['unknown_user'] . ' #' . $uid;
    }
    return $username . ' (#' . $uid . ')';
}

function forms_admin_submissions($formId)
{
    global $_TABLES, $LANG_FORMS;

    $form = forms_get_form($formId);
    if (!$form) {
        return forms_admin_header($LANG_FORMS['invalid_request']) . forms_admin_footer();
    }

    $html = forms_admin_header($LANG_FORMS['stored_submissions_title'] . ': ' . $form['title']);
    $html .= forms_admin_styles();
    if (empty($form['store_results'])) {
        return $html . '<p>' . forms_escape($LANG_FORMS['stored_submissions_disabled']) . '</p>' . forms_admin_footer();
    }

    $html .= '<div class="forms-admin-actions">'
        . '<a class="uk-button" href="index.php?mode=editform&id=' . (int) $formId . '#forms-submissions">' . forms_escape($LANG_FORMS['back_to_form']) . '</a>'
        . '<a class="uk-button" href="index.php?mode=exportcsv&form_id=' . (int) $formId . '">' . forms_escape($LANG_FORMS['export_csv']) . '</a></div>'
        . '<p>' . forms_escape($LANG_FORMS['submissions_list_help']) . '</p>';

    $r = DB_query("SELECT * FROM {$_TABLES['forms_submissions']} WHERE form_id=" . (int) $formId . " ORDER BY submitted DESC LIMIT 100");
    if (DB_numRows($r) < 1) {
        return $html . '<p>' . forms_escape($LANG_FORMS['no_submissions']) . '</p>' . forms_admin_footer();
    }

    $html .= '<table class="forms-admin-table"><thead><tr><th>ID</th><th>' . forms_escape($LANG_FORMS['date']) . '</th><th>'
        . forms_escape($LANG_FORMS['user']) . '</th><th>' . forms_escape($LANG_FORMS['actions']) . '</th></tr></thead><tbody>';
    while ($row = DB_fetchArray($r)) {
        $sid = (int) $row['id'];
        $html .= '<tr><td>#' . $sid . '</td><td>' . date('Y-m-d H:i:s', (int) $row['submitted']) . '</td><td>'
            . forms_escape(forms_admin_user_label($row['uid'])) . '</td><td>'
            . '<a href="index.php?mode=submission&form_id=' . (int) $formId . '&id=' . $sid . '">' . forms_escape($LANG_FORMS['view_details']) . '</a> | '
            . '<a href="index.php?mode=deletesubmission&form_id=' . (int) $formId . '&id=' . $sid . '&amp;' . CSRF_TOKEN . '=' . SEC_createToken()
            . '" onclick="return confirm(\'' . forms_escape($LANG_FORMS['confirm_delete_submission']) . '\')">' . forms_escape($LANG_FORMS['delete']) . '</a></td></tr>';
    }
    $html .= '</tbody></table>';
    return $html . forms_admin_footer();
}

function forms_admin_format_submission_value($field, $value)
{
    global $LANG_FORMS;

    if (!$field) {
        return $value;
    }
    $type = strtolower($field['type']);
    if ($type === 'checkbox' || $type === 'consent') {
        return ((string) $value === '1') ? $LANG_FORMS['yes'] : $LANG_FORMS['no'];
    }
    if ($type === 'select' || $type === 'radio') {
        $options = forms_parse_options($field['options']);
        return isset($options[$value]) ? $options[$value] : $value;
    }
    if ($type === 'checkboxes') {
        $options = forms_parse_options($field['options']);
        $parts = array_filter(array_map('trim', explode(',', $value)), 'strlen');
        $labels = array();
        foreach ($parts as $part) {
            $labels[] = isset($options[$part]) ? $options[$part] : $part;
        }
        return implode(', ', $labels);
    }
    return $value;
}

function forms_admin_submission_detail($formId, $submissionId)
{
    global $_TABLES, $LANG_FORMS;

    $form = forms_get_form($formId);
    if (!$form) {
        return forms_admin_header($LANG_FORMS['invalid_request']) . forms_admin_footer();
    }

    $r = DB_query("SELECT * FROM {$_TABLES['forms_submissions']} WHERE id=" . (int) $submissionId
        . " AND form_id=" . (int) $formId . " LIMIT 1");
    if (DB_numRows($r) < 1) {
        return forms_admin_header($LANG_FORMS['invalid_request']) . '<p>' . forms_escape($LANG_FORMS['submission_not_found']) . '</p>' . forms_admin_footer();
    }
    $submission = DB_fetchArray($r);

    $fieldMap = array();
    $fr = DB_query("SELECT * FROM {$_TABLES['forms_fields']} WHERE form_id=" . (int) $formId . " ORDER BY field_order,id");
    while ($field = DB_fetchArray($fr)) {
        $fieldMap[$field['name']] = $field;
    }

    $values = array();
    $vr = DB_query("SELECT field_name,field_value FROM {$_TABLES['forms_submission_values']} WHERE submission_id=" . (int) $submissionId . " ORDER BY id");
    while ($value = DB_fetchArray($vr)) {
        $values[] = $value;
    }

    $html = forms_admin_header($LANG_FORMS['submission_detail_title'] . ' #' . (int) $submissionId);
    $html .= forms_admin_styles();
    $html .= '<div class="forms-admin-actions"><a class="uk-button" href="index.php?mode=submissions&form_id=' . (int) $formId . '">'
        . forms_escape($LANG_FORMS['back_to_submissions']) . '</a>'
        . '<a class="uk-button uk-button-danger" href="index.php?mode=deletesubmission&form_id=' . (int) $formId . '&id=' . (int) $submissionId
        . '&amp;' . CSRF_TOKEN . '=' . SEC_createToken() . '" onclick="return confirm(\'' . forms_escape($LANG_FORMS['confirm_delete_submission']) . '\')">'
        . forms_escape($LANG_FORMS['delete_submission']) . '</a></div>';

    $html .= '<div class="forms-admin-section"><h2>' . forms_escape($LANG_FORMS['submission_information']) . '</h2><dl>'
        . '<dt><strong>' . forms_escape($LANG_FORMS['form']) . '</strong></dt><dd>' . forms_escape($form['title']) . '</dd>'
        . '<dt><strong>' . forms_escape($LANG_FORMS['date']) . '</strong></dt><dd>' . date('Y-m-d H:i:s', (int) $submission['submitted']) . '</dd>'
        . '<dt><strong>' . forms_escape($LANG_FORMS['user']) . '</strong></dt><dd>' . forms_escape(forms_admin_user_label($submission['uid'])) . '</dd>'
        . (!empty($submission['user_agent']) ? '<dt><strong>' . forms_escape($LANG_FORMS['browser']) . '</strong></dt><dd>' . forms_escape($submission['user_agent']) . '</dd>' : '')
        . '</dl></div>';

    $html .= '<div class="forms-admin-section"><h2>' . forms_escape($LANG_FORMS['submitted_values']) . '</h2><table class="forms-admin-table"><tbody>';
    foreach ($values as $value) {
        $name = $value['field_name'];
        $field = isset($fieldMap[$name]) ? $fieldMap[$name] : false;
        $label = $field ? $field['label'] : $name;
        $displayValue = forms_admin_format_submission_value($field, $value['field_value']);
        $html .= '<tr><th style="width:30%">' . forms_escape($label) . '</th><td>'
            . nl2br(forms_escape($displayValue)) . '</td></tr>';
    }
    $html .= '</tbody></table></div>';
    return $html . forms_admin_footer();
}

function forms_admin_delete_submission($formId, $submissionId)
{
    global $_TABLES;

    $check = DB_query("SELECT id FROM {$_TABLES['forms_submissions']} WHERE id=" . (int) $submissionId
        . " AND form_id=" . (int) $formId . " LIMIT 1");
    if (DB_numRows($check) < 1) {
        return false;
    }
    DB_delete($_TABLES['forms_submission_values'], 'submission_id', (int) $submissionId);
    DB_query("DELETE FROM {$_TABLES['forms_submissions']} WHERE id=" . (int) $submissionId . " AND form_id=" . (int) $formId);
    return !DB_error();
}

function forms_admin_export_csv($formId)
{
    global $_TABLES;$form=forms_get_form($formId);if(!$form||empty($form['store_results']))COM_redirect('index.php');$fields=forms_get_fields($formId);$filename='forms-'.forms_slugify($form['slug']).'-'.date('Ymd-His').'.csv';if(!headers_sent()){header('Content-Type: text/csv; charset=UTF-8');header('Content-Disposition: attachment; filename="'.$filename.'"');header('Pragma: no-cache');header('Expires: 0');}$out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");$head=array('id','date','uid');foreach($fields as $f)$head[]=$f['label'];fputcsv($out,$head,';');$r=DB_query("SELECT * FROM {$_TABLES['forms_submissions']} WHERE form_id=".(int)$formId." ORDER BY submitted ASC");while($sub=DB_fetchArray($r)){$vals=array();$vr=DB_query("SELECT field_name,field_value FROM {$_TABLES['forms_submission_values']} WHERE submission_id=".(int)$sub['id']);while($v=DB_fetchArray($vr))$vals[$v['field_name']]=$v['field_value'];$row=array((int)$sub['id'],date('Y-m-d H:i:s',(int)$sub['submitted']),(int)$sub['uid']);foreach($fields as $f)$row[]=isset($vals[$f['name']])?$vals[$f['name']]:'';fputcsv($out,$row,';');}fclose($out);exit;
}
$mode=isset($_REQUEST['mode'])?$_REQUEST['mode']:'';$display='';
if($mode==='saveform'){forms_admin_save_form();COM_redirect('index.php');}
elseif($mode==='deleteform'){$id=isset($_GET['id'])?(int)$_GET['id']:0;if($id>0&&SEC_checkToken()){DB_query("DELETE FROM {$_TABLES['forms_submission_values']} WHERE submission_id IN (SELECT id FROM {$_TABLES['forms_submissions']} WHERE form_id=$id)");DB_delete($_TABLES['forms_submissions'],'form_id',$id);DB_delete($_TABLES['forms_fields'],'form_id',$id);DB_delete($_TABLES['forms_definitions'],'id',$id);}COM_redirect('index.php');}
elseif($mode==='duplicateform'){$id=isset($_GET['id'])?(int)$_GET['id']:0;if($id>0&&SEC_checkToken()){$newId=forms_admin_duplicate_form($id);if($newId>0)COM_redirect('index.php?mode=editform&id='.$newId);}COM_redirect('index.php');}
elseif($mode==='createtemplate'){$template=isset($_GET['template'])?$_GET['template']:'contact';$allowedTemplates=array('contact','feedback','event','support','quote');if(!in_array($template,$allowedTemplates,true))$template='contact';if(SEC_checkToken()){$newId=forms_admin_create_template($template);COM_redirect('index.php?mode=editform&id='.(int)$newId);}COM_redirect('index.php');}
elseif($mode==='savefield'){$formId=forms_admin_save_field();COM_redirect('index.php?mode=editform&id='.(int)$formId.'#forms-fields');}
elseif($mode==='deletefield'){$id=isset($_GET['id'])?(int)$_GET['id']:0;$formId=isset($_GET['form_id'])?(int)$_GET['form_id']:0;if($id>0&&SEC_checkToken())DB_delete($_TABLES['forms_fields'],'id',$id);COM_redirect('index.php?mode=editform&id='.$formId.'#forms-fields');}
elseif($mode==='duplicatefield'){$id=isset($_GET['id'])?(int)$_GET['id']:0;$formId=isset($_GET['form_id'])?(int)$_GET['form_id']:0;if($id>0&&$formId>0&&SEC_checkToken())forms_admin_duplicate_field($formId,$id);COM_redirect('index.php?mode=editform&id='.$formId.'#forms-fields');}
elseif($mode==='movefield'){$id=isset($_GET['id'])?(int)$_GET['id']:0;$formId=isset($_GET['form_id'])?(int)$_GET['form_id']:0;$direction=isset($_GET['direction'])&&$_GET['direction']==='up'?'up':'down';if($id>0&&$formId>0&&SEC_checkToken())forms_admin_move_field($formId,$id,$direction);COM_redirect('index.php?mode=editform&id='.$formId.'#forms-fields');}
elseif($mode==='submissions'){$display=forms_admin_submissions(isset($_GET['form_id'])?(int)$_GET['form_id']:0);}
elseif($mode==='submission'){$display=forms_admin_submission_detail(isset($_GET['form_id'])?(int)$_GET['form_id']:0,isset($_GET['id'])?(int)$_GET['id']:0);}
elseif($mode==='deletesubmission'){$formId=isset($_GET['form_id'])?(int)$_GET['form_id']:0;$id=isset($_GET['id'])?(int)$_GET['id']:0;if($id>0&&$formId>0&&SEC_checkToken())forms_admin_delete_submission($formId,$id);COM_redirect('index.php?mode=submissions&form_id='.$formId);}
elseif($mode==='exportcsv'){forms_admin_export_csv(isset($_GET['form_id'])?(int)$_GET['form_id']:0);}
elseif($mode==='editform'){$display=forms_admin_form_editor(isset($_GET['id'])?(int)$_GET['id']:0);}
else{$display=forms_admin_list();}
$display=COM_createHTMLDocument($display,array('pagetitle'=>$LANG_FORMS['admin_title']));COM_output($display);
