<?php
require_once __DIR__.'/../../main/inc/global.inc.php';
require_once __DIR__.'/LtiProviderPlugin.php';

api_protect_admin_script();

$plugin = LtiProviderPlugin::create();

$clientId = isset($_GET['client_id']) ? $_GET['client_id'] : '';

if (empty($clientId)) {
    api_not_allowed(true);
}

// Get platform info
$platform = Database::getManager()
    ->getRepository('ChamiloPluginBundle:LtiProvider\Platform')
    ->findOneBy(['clientId' => $clientId]);

if (!$platform) {
    api_not_allowed(true);
}

// Handle license deletion
if (isset($_GET['delete_license']) && isset($_GET['user_license'])) {
    $userLicense = (int) $_GET['user_license'];
    if ($plugin->deactivateUserLicense($userLicense, $clientId)) {
        Display::addFlash(
            Display::return_message($plugin->get_lang('LicenseDeleted'), 'success')
        );
    } else {
        Display::addFlash(
            Display::return_message($plugin->get_lang('ErrorDeletingLicense'), 'error')
        );
    }
    header('Location: '.api_get_self().'?client_id='.$clientId);
    exit;
}

// Create the table
$table = new SortableTable(
    'licenses',
    'getTotalNumberOfLicenses',
    'getLicensesData',
    3,
    100,
    'DESC'
);

$table->set_additional_parameters(['client_id' => $clientId]);

function getLicensesData($from, $number_of_items, $column, $direction) {
    $plugin = LtiProviderPlugin::create();
    $clientId = $_GET['client_id'];
    return $plugin->getLicensesData($from, $number_of_items, $column, $direction, $clientId);
}

function getTotalNumberOfLicenses() {
    $plugin = LtiProviderPlugin::create();
    $clientId = $_GET['client_id'];
    return $plugin->getTotalNumberOfLicenses($clientId);
}

// Set headers
$table->set_header(0, 'ID', false);
$table->set_header(1, get_lang('User'), true);
$table->set_header(2, get_lang('Email'), true);
$table->set_header(3, $plugin->get_lang('ExpeditionDate'), true);
$table->set_header(4, get_lang('Deactivate'), false);

// Set column filters
$table->set_column_filter(4, function ($id, $urlParams, $row) use ($clientId) {
    $actions = [];
    if (!$row[4]) {
        $actions[] = Display::url(
            Display::return_icon('delete.png', get_lang('Deactivate')),
            api_get_self().'?client_id='.$clientId.'&delete_license=1&user_license='.$row[0],
            [
                'onclick' => 'javascript: if(!confirm(\''.addslashes(api_htmlentities(get_lang('ConfirmYourChoice'))).'\')) return false;',
            ]
        );
    }
    return implode(' ', $actions);
});

$interbreadcrumb[] = ['url' => api_get_path(WEB_CODE_PATH).'../plugin/lti_provider/admin.php', 'name' => $plugin->get_lang('plugin_title')];

$template = new Template($plugin->get_lang('Licenses'));
$template->assign('table', $table->return_table());
$template->assign('platform', $platform);

$content = $template->fetch('lti_provider/view/licenses.tpl');
$template->assign('header', $plugin->get_lang('Licenses'));
$template->assign('content', $content);
$template->display_one_col_template();
