<?php

$cidReset = true;

require_once __DIR__.'/../../main/inc/global.inc.php';
require_once __DIR__.'/LtiProviderPlugin.php';

api_protect_admin_script();

$timestamp = time();
$filename = "lti_stats_$timestamp.xls";

// The export below is an HTML table served with an .xls extension/MIME,
// a format Excel opens natively - not a real OOXML (.xlsx) package.
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

$toolId = isset($_GET['tool_id']) ? intval($_GET['tool_id']) : null;
$dateRangeStart = isset($_GET['daterange_start']) ? $_GET['daterange_start'] : null;
$dateRangeEnd = isset($_GET['daterange_end']) ? $_GET['daterange_end'] : null;

$plugin = LtiProviderPlugin::create();

$content = '';
$content .= $plugin::getStatsTable($toolId, $dateRangeStart, $dateRangeEnd);

echo $content;
