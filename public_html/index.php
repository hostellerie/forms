<?php

require_once '../lib-common.php';

if (!in_array('forms', $_PLUGINS)) {
    COM_handle404();
    exit;
}

$slug = isset($_GET['f']) ? trim($_GET['f']) : '';
$display = '';

if ($slug === '') {
    // Forms has no public catalogue. A form must be requested explicitly.
    COM_handle404();
    exit;
}

$form = forms_get_form($slug);
if (!$form || empty($form['is_active'])) {
    COM_handle404($_CONF['site_url'] . '/forms/index.php');
    exit;
}

$errors = array();
$values = array();

// Confirmation is displayed after the POST/Redirect/GET cycle.
if (isset($_GET['submitted']) && $_GET['submitted'] === '1') {
    $message = trim($form['success_message']);
    if ($message === '') {
        $message = $LANG_FORMS['default_success'];
    }
    $display = COM_showMessageText(forms_escape($message), forms_escape($form['title']));
    $display = COM_createHTMLDocument($display, array('pagetitle' => $form['title']));
    COM_output($display);
    exit;
}

if (isset($_POST['forms_submit'])) {
    if (!SEC_checkToken()) {
        $errors['_form'] = $LANG_FORMS['invalid_token'];
    } elseif (!empty($_POST['website'])) {
        $errors['_form'] = $LANG_FORMS['spam_detected'];
    } else {
        $config = forms_get_config();
        $minimum = isset($config['min_submit_seconds']) ? max(0, (int) $config['min_submit_seconds']) : 3;
        $started = isset($_POST['form_started']) ? (int) $_POST['form_started'] : 0;
        if ($started < 1 || time() - $started < $minimum) {
            $errors['_form'] = $LANG_FORMS['spam_detected'];
        }
    }

    if (empty($errors)) {
        $fields = forms_get_fields($form['id']);
        $posted = isset($_POST['fields']) && is_array($_POST['fields']) ? $_POST['fields'] : array();
        list($errors, $values) = forms_validate_submission($form, $fields, $posted);

        if (empty($errors)) {
            $spamParts = array();
            foreach ($values as $spamValue) {
                $spamParts[] = forms_submission_value_to_string($spamValue);
            }
            $spamText = implode("\n", $spamParts);
            if (function_exists('PLG_checkforSpam')) {
                $spamResult = PLG_checkforSpam($spamText, isset($_CONF['spamx']) ? $_CONF['spamx'] : 0);
                if ($spamResult > 0) {
                    $errors['_form'] = $LANG_FORMS['spam_detected'];
                }
            }
        }

        if (empty($errors)) {
            if (!empty($form['store_results'])) {
                forms_store_submission($form, $fields, $values);
            }
            if (!empty($form['email_results'])) {
                forms_mail_submission($form, $fields, $values);
            }

            // Post/Redirect/Get: prevent duplicate submissions on refresh and
            // render the confirmation on a fresh GET request.
            $successUrl = $_CONF['site_url'] . '/forms/index.php?f=' . rawurlencode($form['slug']) . '&submitted=1';
            if (!headers_sent()) {
                header('Location: ' . $successUrl, true, 303);
                exit;
            }

            // Fallback for unusual themes/plugins that already emitted output.
            COM_output(COM_refresh($successUrl));
            exit;
        }
    }
}

$display = forms_render_form($slug, $errors, $values);
$display = COM_createHTMLDocument($display, array('pagetitle' => $form['title']));
COM_output($display);
