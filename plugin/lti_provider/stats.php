<?php

$cidReset = true;

require_once __DIR__.'/../../main/inc/global.inc.php';
require_once __DIR__.'/LtiProviderPlugin.php';

api_protect_admin_script();

$interbreadcrumb[] = ['url' => '../index.php', 'name' => get_lang('PlatformAdmin')];

$plugin = LtiProviderPlugin::create();

$content .= $plugin::printLtiLearningPath();

Display::display_header('Estadisticas');
echo Display::page_header('Estadisticas');


$content .= '<script>
function exportarExcel(toolId, startDate, endDate) {
    window.location.href = "download.php?tool_id=" + toolId + "&daterange_start=" + startDate + "&daterange_end=" + endDate;
}
</script>';

echo $content;

Display::display_footer();
