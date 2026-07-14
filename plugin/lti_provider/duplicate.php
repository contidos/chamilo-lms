<?php
/* For license terms, see /license.txt */

use Chamilo\PluginBundle\Entity\LtiProvider\Platform;

$cidReset = true;

require_once __DIR__.'/../../main/inc/global.inc.php';
require_once __DIR__.'/LtiProviderPlugin.php';

api_protect_admin_script();

if (!isset($_REQUEST['id'])) {
    api_not_allowed(true);
}

$platformId = (int) $_REQUEST['id'];

$plugin = LtiProviderPlugin::create();
$em = Database::getManager();

/** @var Platform $platform */
$platform = $em->find('ChamiloPluginBundle:LtiProvider\Platform', $platformId);

if (!$platform) {
    Display::addFlash(
        Display::return_message($plugin->get_lang('NoPlatform'), 'error')
    );
    header('Location: '.api_get_path(WEB_PLUGIN_PATH).'lti_provider/admin.php');
    exit;
}

$newPlatform = new Platform();
$newPlatform->setName($platform->getName());
$newPlatform->setIssuer($platform->getIssuer());
$newPlatform->setClientId($platform->getClientId());
$newPlatform->setAuthLoginUrl($platform->getAuthLoginUrl());
$newPlatform->setAuthTokenUrl($platform->getAuthTokenUrl());
$newPlatform->setKeySetUrl($platform->getKeySetUrl());
$newPlatform->setDeploymentId($platform->getDeploymentId());
$newPlatform->setKid($platform->getKid());
$newPlatform->setToolProvider($platform->getToolProvider());
$newPlatform->setTotalLicenses($platform->getTotalLicenses());
$newPlatform->setAvailableLicenses($platform->getTotalLicenses());

$em->persist($newPlatform);
$em->flush();

Display::addFlash(
    Display::return_message($plugin->get_lang('PlatformDuplicated'), 'success')
);

header('Location: '.api_get_path(WEB_PLUGIN_PATH).'lti_provider/edit.php?id='.$newPlatform->getId());
exit;
