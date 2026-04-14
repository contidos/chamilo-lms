<?php
/* For license terms, see /license.txt */
use ChamiloSession as Session;

require_once __DIR__.'/../../../main/inc/global.inc.php';
require_once __DIR__.'/../src/LtiProvider.php';
require_once __DIR__.'/../LtiProviderPlugin.php';

$launch = LtiProvider::create()->launch();
if (!$launch->hasNrps()) {
    // throw new Exception("Don't have names and roles!");
}

$launchData = $launch->getLaunchData();

$clientId = $launchData['aud'];

$plugin = LtiProviderPlugin::create();
$toolVars = $plugin->getToolProviderVars($clientId);

// Check if the platform is limited by licenses
$isLimited = $plugin->isPlatformLicenseLimited($clientId);
$hasAvailableLicenses = true;
$userHasLicense = true;
$userId = null;
$continue = true;

if ($isLimited) {
    $hasAvailableLicenses = $plugin->hasAvailableLicenses($clientId);
}

$authSource = IMS_LTI_SOURCE;
$username = md5($launchData['iss'].'_'.$launchData['sub']);
$userInfo = api_get_user_info_from_username($username, $authSource);

$newUser = false;
if (empty($userInfo)) {
    $newUser = true;
}

if ($isLimited) {
    error_log("Checking user license");
    $userHasLicense = $plugin->hasUserLicense($userInfo['id'], $clientId);
    if ($userHasLicense) {
        $continue = true;
    } else {
        $continue = false;
    }
}

if(!$isLimited || $continue ||  $hasAvailableLicenses) {
    $login = LtiProvider::create()->validateUser($launchData, $toolVars['courseCode'], $toolVars['toolName'], $toolVars['sessionId'], $newUser);
}

$ltiSession = [];
if ($login && $hasAvailableLicenses) {
    $userId = api_get_user_id();

    if ($isLimited) {
        error_log("Checking user license");
        $userHasLicense = $plugin->hasUserLicense($userId, $clientId);
    }

    if ($isLimited && $newUser) {
        error_log("Adding user license");
        $plugin->addUserLicense($userId, $clientId);
        $userHasLicense = true;
    }

    $values = [];
    $values['issuer'] = $launchData['iss'];
    $values['user_id'] = $userId;
    $values['client_uid'] = $launchData['sub'];
    $values['course_code'] = $toolVars['courseCode'];
    $values['tool_id'] = $toolVars['toolId'];
    $values['tool_name'] = $toolVars['toolName'];
    $values['lti_launch_id'] = $launch->getLaunchId();
    $values['session_id'] = $toolVars['sessionId'];
    $values['client_id'] = $clientId;
    $plugin->saveResult($values);
    $ltiSession = $values;
}

$cidReq = 'cidReq='.$toolVars['courseCode'].'&id_session='.$toolVars['sessionId'].'&gidReq=0&gradebook=0';

if ('lp' == $toolVars['toolName']) {
    $launchUrl = api_get_path(WEB_CODE_PATH).'lp/lp_controller.php?'.$cidReq.'&action=view&lp_id='.$toolVars['toolId'].'&isStudentView=true&lti_launch_id='.$launch->getLaunchId();
} else {
    $launchUrl = api_get_path(WEB_CODE_PATH).'exercise/overview.php?'.$cidReq.'&origin=embeddable&exerciseId='.$toolVars['toolId'].'&lti_launch_id='.$launch->getLaunchId();
}

if ($hasAvailableLicenses == false && $isLimited && $userHasLicense == false) {
    error_log("No available licenses");
    $launchUrl = api_get_path(WEB_PLUGIN_PATH).'lti_provider/no_license.php?close=1';
    header('Location: '.$launchUrl);
    exit;
}

if($isLimited && $hasAvailableLicenses && $userHasLicense == false) {
    error_log("Adding user license");
    $userId = api_get_user_id();
    $plugin->addUserLicense($userId, $clientId);
}
error_log("Launch URL: ".$launchUrl);

$ltiSession['launch_url'] = $launchUrl;
Session::write('_ltiProvider', $ltiSession);
header('Location: '.$launchUrl);
exit;
