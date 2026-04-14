<?php

/* For licensing terms, see /license.txt */

use ChamiloSession as Session;

require_once __DIR__.'/../inc/global.inc.php';

api_protect_course_script();

$sessionId = api_get_session_id();
$courseId = api_get_course_int_id();
$courseInfo = api_get_course_info();

// Access restrictions.
$is_allowedToTrack = Tracking::isAllowToTrack($sessionId);

if (!$is_allowedToTrack) {
    api_not_allowed(true);
    exit;
}

$action = $_GET['action'] ?? null;
$additionalProfileField = $_GET['additional_profile_field'] ?? [];
$showGlobalInfo = isset($_GET['show_global_info']) && $_GET['show_global_info'] == 1;

$additionalExtraFieldsInfo = [];

$objExtraField = new ExtraField('user');

foreach ($additionalProfileField as $fieldId) {
    $additionalExtraFieldsInfo[$fieldId] = $objExtraField->getFieldInfoByFieldId($fieldId);
}

$defaultExtraFields = [];
$defaultExtraFieldsFromSettings = api_get_configuration_value('course_log_default_extra_fields');
$defaultExtraInfo = [];

if (!empty($defaultExtraFieldsFromSettings) && isset($defaultExtraFieldsFromSettings['extra_fields'])) {
    $defaultExtraFields = $defaultExtraFieldsFromSettings['extra_fields'];

    foreach ($defaultExtraFields as $fieldName) {
        $extraFieldInfo = UserManager::get_extra_field_information_by_name($fieldName);

        if (!empty($extraFieldInfo)) {
            $defaultExtraInfo[$extraFieldInfo['id']] = UserManager::get_extra_field_information_by_name($fieldName);
        }
    }
}

$lps = new LearnpathList(
    api_get_user_id(),
    $courseInfo,
    $sessionId,
    null,
    false,
    null,
    true
);
$lps = $lps->get_flat_list();

Session::write('lps', $lps);

/**
 * Prepares the shared SQL query for the user table.
 * See get_user_data() and get_number_of_users().
 *
 * @param bool $getCount Whether to count, or get data
 *
 * @return string SQL query
 */
function prepare_user_sql_query($getCount)
{
    $sql = '';
    $user_table = Database::get_main_table(TABLE_MAIN_USER);
    $admin_table = Database::get_main_table(TABLE_MAIN_ADMIN);

    if ($getCount) {
        $sql .= "SELECT COUNT(u.id) AS total_number_of_items FROM $user_table u";
    } else {
        $sql .= 'SELECT u.id AS col0, u.official_code AS col2, ';

        if (api_is_western_name_order()) {
            $sql .= 'u.firstname AS col3, u.lastname AS col4, ';
        } else {
            $sql .= 'u.lastname AS col3, u.firstname AS col4, ';
        }

        $sql .= " u.username AS col5,
                    u.email AS col6,
                    u.status AS col7,
                    u.active AS col8,
                    u.registration_date AS col9,
                    u.last_login as col10,
                    u.id AS col11,
                    u.expiration_date AS exp,
                    u.password
                FROM $user_table u";
    }

    // adding the filter to see the user's only of the current access_url
    if ((api_is_platform_admin() || api_is_session_admin()) && api_get_multiple_access_url()) {
        $access_url_rel_user_table = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);
        $sql .= " INNER JOIN $access_url_rel_user_table url_rel_user
                  ON (u.id=url_rel_user.user_id)";
    }

    $keywordList = [
        'keyword_firstname',
        'keyword_lastname',
        'keyword_username',
        'keyword_email',
        'keyword_officialcode',
        'keyword_status',
        'keyword_active',
        'keyword_inactive',
        'check_easy_passwords',
    ];

    $keywordListValues = [];
    $atLeastOne = false;
    foreach ($keywordList as $keyword) {
        $keywordListValues[$keyword] = null;
        if (isset($_GET[$keyword]) && !empty($_GET[$keyword])) {
            $keywordListValues[$keyword] = $_GET[$keyword];
            $atLeastOne = true;
        }
    }

    if ($atLeastOne == false) {
        $keywordListValues = [];
    }

    if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
        $keywordFiltered = Database::escape_string("%".$_GET['keyword']."%");
        $sql .= " WHERE (
                    u.firstname LIKE '$keywordFiltered' OR
                    u.lastname LIKE '$keywordFiltered' OR
                    concat(u.firstname, ' ', u.lastname) LIKE '$keywordFiltered' OR
                    concat(u.lastname,' ',u.firstname) LIKE '$keywordFiltered' OR
                    u.username LIKE '$keywordFiltered' OR
                    u.official_code LIKE '$keywordFiltered' OR
                    u.email LIKE '$keywordFiltered'
                )
        ";
    } elseif (isset($keywordListValues) && !empty($keywordListValues)) {
        $query_admin_table = '';
        $keyword_admin = '';

        if (isset($keywordListValues['keyword_status']) &&
            $keywordListValues['keyword_status'] == PLATFORM_ADMIN
        ) {
            $query_admin_table = " , $admin_table a ";
            $keyword_admin = ' AND a.user_id = u.id ';
            $keywordListValues['keyword_status'] = '%';
        }

        $keyword_extra_value = '';
        $sql .= " $query_admin_table
            WHERE (
                u.firstname LIKE '".Database::escape_string("%".$keywordListValues['keyword_firstname']."%")."' AND
                u.lastname LIKE '".Database::escape_string("%".$keywordListValues['keyword_lastname']."%")."' AND
                u.username LIKE '".Database::escape_string("%".$keywordListValues['keyword_username']."%")."' AND
                u.email LIKE '".Database::escape_string("%".$keywordListValues['keyword_email']."%")."' AND
                u.status LIKE '".Database::escape_string($keywordListValues['keyword_status'])."' ";
        if (!empty($keywordListValues['keyword_officialcode'])) {
            $sql .= " AND u.official_code LIKE '".Database::escape_string("%".$keywordListValues['keyword_officialcode']."%")."' ";
        }

        $sql .= "
            $keyword_admin
            $keyword_extra_value
        ";

        if (isset($keywordListValues['keyword_active']) &&
            !isset($keywordListValues['keyword_inactive'])
        ) {
            $sql .= ' AND u.active = 1';
        } elseif (isset($keywordListValues['keyword_inactive']) &&
            !isset($keywordListValues['keyword_active'])
        ) {
            $sql .= ' AND u.active = 0';
        }
        $sql .= ' ) ';
    }

    $preventSessionAdminsToManageAllUsers = api_get_setting('prevent_session_admins_to_manage_all_users');
    if (api_is_session_admin() && $preventSessionAdminsToManageAllUsers === 'true') {
        $sql .= ' AND u.creator_id = '.api_get_user_id();
    }

    $variables = Session::read('variables_to_show', []);
    if (!empty($variables)) {
        $extraField = new ExtraField('user');
        $extraFieldResult = [];
        $extraFieldHasData = [];
        foreach ($variables as $variable) {
            if (isset($_GET['extra_'.$variable])) {
                if (is_array($_GET['extra_'.$variable])) {
                    $values = $_GET['extra_'.$variable];
                } else {
                    $values = [$_GET['extra_'.$variable]];
                }

                if (empty($values)) {
                    continue;
                }

                $info = $extraField->get_handler_field_info_by_field_variable(
                    $variable
                );

                if (empty($info)) {
                    continue;
                }

                foreach ($values as $value) {
                    if (empty($value)) {
                        continue;
                    }
                    if ($info['field_type'] == ExtraField::FIELD_TYPE_TAG) {
                        $result = $extraField->getAllUserPerTag(
                            $info['id'],
                            $value
                        );
                        $result = empty($result) ? [] : array_column(
                            $result,
                            'user_id'
                        );
                    } else {
                        $result = UserManager::get_extra_user_data_by_value(
                            $variable,
                            $value
                        );
                    }
                    $extraFieldHasData[] = true;
                    if (!empty($result)) {
                        $extraFieldResult = array_merge(
                            $extraFieldResult,
                            $result
                        );
                    }
                }
            }
        }

        if (!empty($extraFieldHasData)) {
            $sql .= " AND (u.id IN ('".implode("','", $extraFieldResult)."')) ";
        }
    }

    // adding the filter to see the user's only of the current access_url
    if ((api_is_platform_admin() || api_is_session_admin()) &&
        api_get_multiple_access_url()
    ) {
        $sql .= ' AND url_rel_user.access_url_id = '.api_get_current_access_url_id();
    }

    return $sql;
}

function getCount()
{
    $sessionId = api_get_session_id();
    $courseCode = api_get_course_id();

    if (empty($sessionId)) {
        // Registered students in a course outside session.
        $count = CourseManager::get_student_list_from_course_code(
            $courseCode,
            false,
            null,
            null,
            null,
            null,
            null,
            true
        );
    } else {
        // Registered students in session.
        $count = CourseManager::get_student_list_from_course_code(
            $courseCode,
            true,
            $sessionId,
            null,
            null,
            null,
            null,
            true
        );
    }

    return $count;
}

/**
 * Get the users to display on the current page (fill the sortable-table).
 *
 * @param   int     offset of first user to recover
 * @param   int     Number of users to get
 * @param   int     Column to sort on
 * @param   string  Order (ASC,DESC)
 *
 * @return array Users list
 *
 * @see SortableTable#get_table_data($from)
 */
$getData = function ($from, $numberOfItems, $column, $direction) use ($additionalExtraFieldsInfo, $showGlobalInfo, $courseInfo) {
    $sessionId = api_get_session_id();
    $courseCode = api_get_course_id();
    $courseId = api_get_course_int_id();

    $lps = Session::read('lps');

    if (empty($sessionId)) {
        // Registered students in a course outside session.
        $students = CourseManager::get_student_list_from_course_code(
            $courseCode,
            false,
            null,
            null,
            null,
            null,
            null,
            false,
            $from,
            $numberOfItems
        );
    } else {
        // Registered students in session.
        $students = CourseManager::get_student_list_from_course_code(
            $courseCode,
            true,
            $sessionId,
            null,
            null,
            null,
            null,
            false,
            $from,
            $numberOfItems
        );
    }

    $useNewTable = Tracking::minimumTimeAvailable($sessionId, $courseId);

    // Calculate survey data once for all students (if not in session and showGlobalInfo is enabled)
    $surveyUserList = [];
    $totalSurveys = 0;
    if ($showGlobalInfo && empty($sessionId)) {
        $surveyList = SurveyManager::get_surveys($courseCode, $sessionId);
        if ($surveyList) {
            $totalSurveys = count($surveyList);
            foreach ($surveyList as $survey) {
                $userList = SurveyManager::get_people_who_filled_survey(
                    $survey['survey_id'],
                    false,
                    $courseId
                );
                foreach ($userList as $user_id) {
                    isset($surveyUserList[$user_id]) ? $surveyUserList[$user_id]++ : $surveyUserList[$user_id] = 1;
                }
            }
        }
    }

    $users = [];
    foreach ($students as $student) {
        $user = [];
        $userId = $student['id'];

        // Get user info for official code
        $userInfo = api_get_user_info($userId);
        $user[] = $userInfo['official_code'];

        $user[] = $student['firstname'];
        $user[] = $student['lastname'];
        $user[] = $userInfo['email'];
        $user[] = $student['username'];

        $objExtraValue = new ExtraFieldValue('user');

        foreach ($additionalExtraFieldsInfo as $fieldInfo) {
            $extraValue = $objExtraValue->get_values_by_handler_and_field_id($student['id'], $fieldInfo['id'], true);
            $user[] = $extraValue['value'] ?? null;
        }

        // Add consolidated course metrics (only if showGlobalInfo is enabled)
        if ($showGlobalInfo) {
            $totalTime = Tracking::get_time_spent_on_the_course($userId, $courseId, $sessionId);
            $user[] = api_time_to_hms($totalTime);

            $courseProgress = Tracking::get_avg_student_progress($userId, $courseCode, [], $sessionId);
            $user[] = $courseProgress.'%';

            // Get first connection date - use a more robust query
            $table = Database::get_main_table(TABLE_STATISTIC_TRACK_E_COURSE_ACCESS);
            $sql = "SELECT LEAST(
                        COALESCE(MIN(login_course_date), '9999-12-31'),
                        COALESCE(MIN(logout_course_date), '9999-12-31')
                    ) as first_date
                    FROM $table
                    WHERE user_id = $userId
                    AND c_id = $courseId
                    AND session_id = $sessionId
                    AND (login_course_date IS NOT NULL OR logout_course_date IS NOT NULL)";
            $rs = Database::query($sql);
            $firstConnection = '-';
            if (Database::num_rows($rs) > 0) {
                $firstDate = Database::result($rs, 0, 0);
                if (!empty($firstDate) && $firstDate != '9999-12-31') {
                    $firstConnection = api_convert_and_format_date($firstDate, DATE_FORMAT_SHORT);
                }
            }
            $user[] = $firstConnection;

            $lastConnection = Tracking::get_last_connection_date_on_the_course($userId, $courseInfo, $sessionId);
            $user[] = $lastConnection ? $lastConnection : '-';
        }

        $lpTimeList = [];
        if ($useNewTable) {
            $lpTimeList = Tracking::getCalculateTime($userId, $courseId, $sessionId);
        }
        foreach ($lps as $lp) {
            $lpId = $lp['iid'];
            $progress = Tracking::get_avg_student_progress(
                $userId,
                $courseCode,
                [$lpId],
                $sessionId
            );

            if ($useNewTable) {
                $time = isset($lpTimeList[TOOL_LEARNPATH][$lpId]) ? $lpTimeList[TOOL_LEARNPATH][$lpId] : 0;
            } else {
                $time = Tracking::get_time_spent_in_lp(
                    $userId,
                    $courseCode,
                    [$lpId],
                    $sessionId
                );
            }
            $time = api_time_to_hms($time);

            $first = Tracking::getFirstConnectionTimeInLp(
                $userId,
                $courseCode,
                $lpId,
                $sessionId
            );

            $first = !empty($first) ? api_convert_and_format_date(
                $first,
                DATE_TIME_FORMAT_LONG
            ) : '-';

            $last = Tracking::get_last_connection_time_in_lp(
                $userId,
                $courseCode,
                $lpId,
                $sessionId
            );
            $last = !empty($last) ? api_convert_and_format_date(
                $last,
                DATE_TIME_FORMAT_LONG
            ) : '-';

            $user[] = $progress;
            $user[] = $first;
            $user[] = $last;
            $user[] = $time;
        }

        $users[] = $user;
    }

    return $users;
};

$interbreadcrumb[] = [
    'url' => api_get_path(WEB_CODE_PATH).'tracking/courseLog.php?'.api_get_cidreq(),
    'name' => get_lang('Tracking'),
];

$tool_name = get_lang('CourseLearningPathsGenericStats');

$headers = [];
$headers[] = get_lang('OfficialCode');
$headers[] = get_lang('FirstName');
$headers[] = get_lang('LastName');
$headers[] = get_lang('Email');
$headers[] = get_lang('Username');

$parameters = [];
$parameters['sec_token'] = Security::get_token();
$parameters['cidReq'] = api_get_course_id();
$parameters['id_session'] = api_get_session_id();

foreach ($additionalExtraFieldsInfo as $fieldInfo) {
    $headers[] = $fieldInfo['display_text'];
    $parameters['additional_profile_field'] = $fieldInfo['id'];
}

// Add consolidated course metrics (only if showGlobalInfo is enabled)
if ($showGlobalInfo) {
    $headers[] = get_lang('TrainingTime');
    $headers[] = get_lang('CourseProgress');
    $headers[] = get_lang('FirstLoginInCourse');
    $headers[] = get_lang('LatestLoginInCourse');
}

foreach ($lps as $lp) {
    $lpName = $lp['lp_name'];
    $headers[] = get_lang('Progress').': '.$lpName;
    $headers[] = get_lang('FirstAccess').': '.$lpName;
    $headers[] = get_lang('LastAccess').': '.$lpName;
    $headers[] = get_lang('Time').': '.$lpName;
}

if (!empty($action)) {
    switch ($action) {
        case 'export':
            $data = $getData(0, 100000, null, null);
            $data = array_merge([$headers], $data);
            $name = api_get_course_id().'_'.get_lang('Learnpath').'_'.get_lang('Export');
            Export::arrayToXls($data, $name);
            exit;
            break;
    }
}

$actionsLeft = TrackingCourseLog::actionsLeft('lp');
$actionsCenter = '';

$exportParams = [
    'action' => 'export',
    'additional_profile_field' => $additionalProfileField,
];
if ($showGlobalInfo) {
    $exportParams['show_global_info'] = 1;
}
$actionsRight = Display::url(
    Display::return_icon(
        'export_excel.png',
        get_lang('ExportAsXLS'),
        null,
        ICON_SIZE_MEDIUM
    ),
    api_get_self().'?'.api_get_cidreq().'&'.http_build_query($exportParams)
);

// Create a sortable table with user-data
$table = new SortableTable(
    'lps',
    'getCount',
    $getData
);
$table->set_additional_parameters($parameters);
$column = 0;
foreach ($headers as $header) {
    $table->set_header($column++, $header, false);
}

// Add toggle button for global information
$globalInfoParams = [
    'cidReq' => api_get_course_id(),
    'id_session' => api_get_session_id(),
    'additional_profile_field' => $additionalProfileField,
];
if ($showGlobalInfo) {
    $globalInfoParams['show_global_info'] = 0;
    $globalInfoText = 'OCULTAR INFORMACIÓN GLOBAL';
} else {
    $globalInfoParams['show_global_info'] = 1;
    $globalInfoText = 'MOSTRAR INFORMACIÓN GLOBAL';
}
$globalInfoUrl = api_get_self().'?'.http_build_query($globalInfoParams);
$globalInfoButton = Display::url(
    $globalInfoText,
    $globalInfoUrl,
    ['class' => 'btn btn-primary']
);

$content = [];
$profileFieldsSelector = TrackingCourseLog::displayAdditionalProfileFields($defaultExtraFields, api_get_self());
$buttonsRow = '<div style="margin-bottom: 10px;">'.$profileFieldsSelector.' '.$globalInfoButton.'</div>';
$content[] = $buttonsRow;
$content[] = $table->return_table();
$toolbarActions = Display::toolbarAction(
    'toolbarUser',
    [$actionsLeft, $actionsCenter, $actionsRight],
    [4, 4, 4]
);

$tpl = new Template($tool_name);
$tpl->assign('actions', $toolbarActions);
$tpl->assign('content', implode(PHP_EOL, $content));
$tpl->display_one_col_template();
