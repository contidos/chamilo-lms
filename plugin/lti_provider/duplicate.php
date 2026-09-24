<?php
/* For license terms, see /license.txt */

use Chamilo\PluginBundle\Entity\LtiProvider\Platform;
use Chamilo\PluginBundle\LtiProvider\Form\FrmAdd;

$cidReset = true;

require_once __DIR__.'/../../main/inc/global.inc.php';
require_once __DIR__.'/src/Form/FrmAdd.php';
require_once __DIR__.'/LtiProviderPlugin.php';

api_protect_admin_script();

$htmlHeadXtra[] = '<script>
function selectToolProvider(tool) {
    $(".sbox-tool").each(function() {
        if ($(this).hasClass("select2-hidden-accessible")) {
            $(this).select2("destroy");
        }
    });
    $(".sbox-tool").attr("disabled", "disabled");
    $(".select-tool").hide();
    $("#select-" + tool).show();
    var $select = $("#sbox-tool-" + tool);
    $select.removeAttr("disabled");
    if ($.fn.select2) {
        $select.select2({ width: "100%" });
    }
}
$(function() {
    var $radios = $("input[name=\'tool_type\']");
    if ($radios.length > 0) {
        selectToolProvider($radios.filter(":checked").val() || "quiz");
        $radios.on("change", function() {
            selectToolProvider($(this).val());
        });
    }
});
</script>';

if (!isset($_REQUEST['id'])) {
    api_not_allowed(true);
}

$sourcePlatformId = (int) $_REQUEST['id'];

$plugin = LtiProviderPlugin::create();
$em = Database::getManager();

/** @var Platform $sourcePlatform */
$sourcePlatform = $em->find('ChamiloPluginBundle:LtiProvider\Platform', $sourcePlatformId);

if (!$sourcePlatform) {
    Display::addFlash(
        Display::return_message($plugin->get_lang('NoPlatform'), 'error')
    );
    header('Location: '.api_get_path(WEB_PLUGIN_PATH).'lti_provider/admin.php');
    exit;
}

// GET only pre-fills a normal "add platform" form from the source platform's
// data; nothing is persisted until the admin reviews and submits it (POST).
$form = new FrmAdd('lti_provider_duplicate_platform', [], $sourcePlatform);
$form->build();

if ($form->validate()) {
    $formValues = $form->exportValues();

    if ($formValues['licenses'] == null || $formValues['licenses'] < 0 || $formValues['licenses'] > 9999) {
        $formValues['licenses'] = "0";
    }

    $newPlatform = new Platform();
    $newPlatform->setName($formValues['name']);
    $newPlatform->setIssuer($formValues['issuer']);
    $newPlatform->setClientId($formValues['client_id']);
    $newPlatform->setAuthLoginUrl($formValues['auth_login_url']);
    $newPlatform->setAuthTokenUrl($formValues['auth_token_url']);
    $newPlatform->setKeySetUrl($formValues['key_set_url']);
    $newPlatform->setDeploymentId($formValues['deployment_id']);
    $newPlatform->setKid($formValues['kid']);
    $toolProvider = (isset($formValues['tool_provider']) ? $formValues['tool_provider'] : $_POST['tool_provider']);
    $newPlatform->setToolProvider($toolProvider);
    $newPlatform->setTotalLicenses($formValues['licenses']);
    $newPlatform->setAvailableLicenses($formValues['licenses']);

    $em->persist($newPlatform);
    $em->flush();

    Display::addFlash(
        Display::return_message($plugin->get_lang('PlatformDuplicated'), 'success')
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
