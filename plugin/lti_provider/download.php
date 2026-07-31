<?php

$cidReset = true;

require_once __DIR__.'/../../main/inc/global.inc.php';
require_once __DIR__.'/LtiProviderPlugin.php';

api_protect_admin_script();

$timestamp = time();
$filename = "lti_stats_$timestamp.xls";

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

$toolId = isset($_GET['tool_id']) ? intval($_GET['tool_id']) : null;
$dateRangeStart = isset($_GET['daterange_start']) ? $_GET['daterange_start'] : null;
$dateRangeEnd = isset($_GET['daterange_end']) ? $_GET['daterange_end'] : null;

$plugin = LtiProviderPlugin::create();

$content .= $plugin::getStatsTable($toolId, $dateRangeStart, $dateRangeEnd);

echo $content;
