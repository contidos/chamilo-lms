<?php
/* For license terms, see /license.txt */

$cidReset = true;

require_once __DIR__.'/../../main/inc/global.inc.php';
use Chamilo\PluginBundle\Entity\LtiProvider\Platform;
use Chamilo\PluginBundle\LtiProvider\Form\FrmAdd;

require_once __DIR__.'/LtiProviderPlugin.php';

api_protect_admin_script();

$plugin = LtiProviderPlugin::create();

$em = Database::getManager();

$form = new FrmAdd('lti_provider_create_platform');
$form->build();

if ($form->validate()) {
    $formValues = $form->exportValues();

    if($formValues['licenses'] == null || $formValues['licenses'] < 0 || $formValues['licenses'] > 9999) {
        $formValues['licenses'] = "0";
    }
    
    $platform = new Platform();
    $platform->setName($formValues['name']);
    $platform->setIssuer($formValues['issuer']);
    $platform->setClientId($formValues['client_id']);
    $platform->setAuthLoginUrl($formValues['auth_login_url']);
    $platform->setAuthTokenUrl($formValues['auth_token_url']);
    $platform->setKeySetUrl($formValues['key_set_url']);
    $platform->setDeploymentId($formValues['deployment_id']);
    $platform->setKid($formValues['kid']);
    $toolProvider = (isset($formValues['tool_provider']) ? $formValues['tool_provider'] : $_POST['tool_provider']);
    $platform->setToolProvider($toolProvider);
    $platform->setTotalLicenses($formValues['licenses']);
    $platform->setAvailableLicenses($formValues['licenses']);

    $em->persist($platform);
    $em->flush();

    Display::addFlash(
        Display::return_message($plugin->get_lang('PlatformConnectionAdded'), 'success')
    );

    header('Location: '.api_get_path(WEB_PLUGIN_PATH).'lti_provider/admin.php');
    exit;
}

$form->setDefaultValues();

$interbreadcrumb[] = ['url' => api_get_path(WEB_CODE_PATH).'admin/index.php', 'name' => get_lang('PlatformAdmin')];
$interbreadcrumb[] = ['url' => api_get_path(WEB_PLUGIN_PATH).'lti_provider/admin.php', 'name' => $plugin->get_title()];

$pageTitle = $plugin->get_lang('AddPlatform');

$template = new Template($pageTitle);
$template->assign('form', $form->returnForm());

$content = $template->fetch('lti_provider/view/add.tpl');

$template->assign('header', $pageTitle);
$template->assign('content', $content);
$template->display_one_col_template();
