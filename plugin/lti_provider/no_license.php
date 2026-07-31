<?php

$cidReset = true;

require_once __DIR__.'/../../main/inc/global.inc.php';
require_once __DIR__.'/LtiProviderPlugin.php';

api_protect_admin_script();

$plugin = LtiProviderPlugin::create();

if ($plugin->get('enabled') !== 'true') {
    api_not_allowed(true);
}

// Display the page header
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_PLUGIN_PATH).'lti_provider/assets/css/style.css">';

$tittle = $plugin->get_lang('NoLicense');

// Display the no license message
$content = '<div class="row">';
$content .=  '    <div class="col-md-12">';
$content .= '        <div class="alert alert-warning">';
$content .= '            <h4><i class="fa fa-exclamation-triangle"></i> '.$plugin->get_lang('NoLicense').'</h4>';
$content .= '            <p>'.$plugin->get_lang('NoLicenseMessage').'</p>';
$content .= '        </div>';
$content .= '    </div>';
$content .= '</div>';

$template = new Template($tittle);
$template->assign('form', $form->returnForm());

$content = $template->fetch('lti_provider/view/no_license.tpl');

$template->assign('content', $content);
$template->display_one_col_template();
