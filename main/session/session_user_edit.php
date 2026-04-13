<?php
/* For licensing terms, see /license.txt */

$cidReset = true;

// including the global Chamilo file
require_once __DIR__.'/../inc/global.inc.php';

$sessionId = isset($_GET['session_id']) ? $_GET['session_id'] : null;
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;

SessionManager::protectSession($sessionId);

$sessionInfo = api_get_session_info($sessionId);

if (empty($sessionInfo)) {
    api_not_allowed(true);
}

if (!isset($sessionInfo['duration']) ||
    (isset($sessionInfo['duration']) && empty($sessionInfo['duration']))
) {
    api_not_allowed(true);
}

if (empty($sessionId) || empty($userId)) {
    api_not_allowed(true);
}

$interbreadcrumb[] = ['url' => 'session_list.php', 'name' => get_lang('SessionList')];
$interbreadcrumb[] = [
    'url' => "resume_session.php?id_session=".$sessionId,
    "name" => get_lang('SessionOverview'),
];

$form = new FormValidator('edit', 'post', api_get_self().'?session_id='.$sessionId.'&user_id='.$userId);
$form->addHeader(get_lang('EditUserSessionDuration'));
$userInfo = api_get_user_info($userId);

// Show current end date for the session for this user, if any
$userAccess = CourseManager::getFirstCourseAccessPerSessionAndUser(
    $sessionId,
    $userId
);

$extension = 0;

if (count($userAccess) == 0) {
    // User never accessed the session. End date is still open
    //$msg = sprintf(get_lang('UserNeverAccessedSessionDefaultDurationIsX'), $sessionInfo['duration']);

    $msg = 'El usuario nunca ha accedido a la sesión. La duración por defecto es de ' . $sessionInfo['duration'] . ' días';

} else {
    $duration = $sessionInfo['duration'];

    // The user already accessed the session. Show a clear detail of the days count.
    $days = SessionManager::getDayLeftInSession($sessionInfo, $userId);
    $subscription = SessionManager::getUserSession($userId, $sessionId);
    $extension = $subscription['duration'];
    $firstAccess = api_strtotime($userAccess['login_course_date'], 'UTC');
    $firstAccessString = api_convert_and_format_date($userAccess['login_course_date'], DATE_FORMAT_SHORT, 'UTC');
    if ($days > 0) {


        if (!empty($subscription['duration'])) {
            $duration = $duration + $subscription['duration'];
        }

        //$msg = sprintf(get_lang('FirstAccessWasXSessionDurationYEndDateInZDays'), $firstAccessString, $duration, $days);

        $msgFirstAccess = '📅 El primer acceso de este usuario a la sesión fue el ' .  $firstAccessString;

        if($extension == 0){
            $msgDays = '⏱️ La duración de la sesión para este usuario es de ' .  $duration . ' días';
        } else {
            $msgDays = '⏱️ La duración de la sesión para este usuario es de ' .  $duration . ' días ('. $sessionInfo['duration'] . ' dias por defecto + ' . $subscription['duration'] . ' dias de extensión)';
        }

        $msgRemaingDays = '✅ Al usuario le queda ' . $days. ' días de acceso';

    } else {
        if (!empty($subscription['duration'])) {
            $duration = $duration + $subscription['duration'];
        }
        $endDateInSeconds = $firstAccess + $duration * 24 * 60 * 60;
        $last = api_convert_and_format_date($endDateInSeconds, DATE_FORMAT_SHORT);
        //$msg = sprintf(get_lang('FirstAccessWasXSessionDurationYEndDateWasZ'), $firstAccessString, $duration, $last);

        $msgFirstAccess = '📅 El primer acceso de este usuario a la sesión fue el ' .  $firstAccessString;



        if($extension == 0){
            $msgDays = '⏱️ La duración de la sesión para este usuario fue de ' .  $duration . ' días';
        } else {
            $msgDays = '⏱️ La duración de la sesión para este usuario fue de ' .  $duration . ' días ('. $sessionInfo['duration'] . ' dias por defecto + ' . $subscription['duration'] . ' dias de extensión)';
        }

        $msgRemaingDays = '❌ El usuario no tiene acceso a la sesión desde el día ' . $last;
    }

    $msg = '<p>' . $msgFirstAccess . '</p>';
    $msg .= '<p>' . $msgDays . '</p>';
    $msg .= '<p>' . $msgRemaingDays . '</p>';
}

$header =  '<div class="row">';
$header .= '<div class="col-sm-5">';
$header .= '<div class="thumbnail">';
$header .= Display::img($userInfo['avatar'], $userInfo['complete_name']);
$header .= '</div>';
$header .= '</div>';

$header .= '<div class="col-sm-7">';

$userData = $userInfo['complete_name']
    .PHP_EOL
    .$user_info['mail']
    .PHP_EOL
    .$user_info['official_code'];

$header .= '<h3>Usuario: ' . Display::url(
    $userData,
    api_get_path(WEB_CODE_PATH).'social/profile.php?u='.$user_info['user_id']
) .'</h3>';

$header .= '<p><h3>Sesión: ' . $sessionInfo['name'] . '<h3></p>';

$header .= '</div>';
$header .='</div>';

$header .=  '<div class="row">';
$header .= '<div class="col-sm-12">';

$header .= $msg;
$header .= '<p>'.'</p>';

$header .= '</div>';
$header .= '</div>';



$form->addElement('html', $header);
//$form->addElement('html', sprintf(get_lang('UserXSessionY'), $userInfo['complete_name'], $sessionInfo['name']));
//$form->addElement('html', '<br>');
//$form->addElement('html', $msg);

$formData =  '<div class="row">';
$formData .= '<div class="col-sm-12">';

$form->addElement('html', $formData);
$form->addElement('number', 'duration', [get_lang('ExtraDurationForUser'), '⚠️ El valor indicando es la cantidad de días DESDE la fecha de PRIMER ACCESO', get_lang('Days')]);
$form->addButtonSave(get_lang('Save'));


$formData = '</div>';
$formData .= '</div>';

$form->addElement('html', $formData);

$form->setDefaults(['duration' => $extension]);
$message = null;
if ($form->validate()) {
    $duration = $form->getSubmitValue('duration');

    SessionManager::editUserSessionDuration($duration, $userId, $sessionId);
    $message = Display::return_message(get_lang('ItemUpdated'), 'confirmation');

    $url = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
    header("Location: " . $url);
    exit();
}

// display the header
Display::display_header(get_lang('Edit'));

echo $message;
$form->display();

Display::display_footer();
