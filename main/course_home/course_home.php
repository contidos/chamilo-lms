<?php
/* For licensing terms, see /license.txt */

use ChamiloSession as Session;
use Fhaculty\Graph\Graph;

/**
 * HOME PAGE FOR EACH COURSE.
 *
 * This page, included in every course's index.php is the home
 * page. To make administration simple, the teacher edits his
 * course from the home page. Only the login detects that the
 * visitor is allowed to activate, deactivate home page links,
 * access to the teachers tools (statistics, edit forums...).
 *
 * Edit visibility of tools
 *
 *   visibility = 1 - everybody
 *   visibility = 0 - course admin (teacher) and platform admin
 *
 * Who can change visibility ?
 *
 *   admin = 0 - course admin (teacher) and platform admin
 *   admin = 1 - platform admin
 *
 * Show message to confirm that a tools must be hide from available tools
 *
 *   visibility 0,1
 *
 * @package chamilo.course_home
 */
$use_anonymous = true;
require_once __DIR__.'/../inc/global.inc.php';

$js = '<script>'.api_get_language_translate_html().'</script>';
$htmlHeadXtra[] = $js;
$htmlHeadXtra[] = '<script>
    /* show eye for all show/hide*/
    function buttonForAllShowHide()
    {
        tools_invisibles = [];
        tools_visibles = [];
        $.each($(".make_visible_and_invisible").parent(), function (index, item) {
            var element = $(item).find("a");
            image = $(element[0]).find("em")[0];
            // extract the tool ID from the HTML em id, removing linktool_
            image_id = $(image).attr("id").replace("linktool_","");
            if (!$(image).hasClass("fa-eye-slash")) {
                // if the image does not have the eye-slash icon, prepare to make invisible
                tools_invisibles.push(image_id)
            } else {
                // if the image has the eye-slash icon, prepare to make visible
                tools_visibles.push(image_id)
            }
        });
        if (tools_visibles.length == 0) {
            $(".visible-all").addClass("hidden");
            $(".invisible-all").removeClass("hidden");
        } else {
            $(".visible-all").removeClass("hidden");
            $(".invisible-all").addClass("hidden");
        }
    }

    /* option show/hide thematic-block */
    $(function() {
        buttonForAllShowHide();
        /* option show/hide all*/
        $(".show-hide-all-tools").on("click" , function() {
            $(".show-hide-all-tools").addClass("disabled");
            tools_invisibles = [];
            tools_visibles = [];
            $.each($(".make_visible_and_invisible").parent(), function (index, item) {
                var element = $(item).find("a");
                image = $(element[0]).find("em")[0];
                image_id = $(image).attr("id").replace("linktool_","");
                if (!$(image).hasClass("fa-eye-slash")) {
                    tools_invisibles.push(image_id)
                } else {
                    tools_visibles.push(image_id)
                }
            });
            message_invisible = "'.get_lang('ToolIsNowHidden').'";
            ids = tools_invisibles;
            if (tools_invisibles.length == 0) {
                ids = tools_visibles;
                message_invisible = "'.get_lang('ToolIsNowVisible').'";
            }

            $.ajax({
                contentType: "application/x-www-form-urlencoded",
                beforeSend: function (myObject) {
                    $(".normal-message").show();
                    $("#id_confirmation_message").hide();
                },
                type: "GET",
                url: "'.api_get_path(WEB_AJAX_PATH).'course_home.ajax.php?'.api_get_cidreq().'&a=set_visibility_for_all",
                data: "tools_ids=" + JSON.stringify(ids) + "&sent_http_request=1",
                success: function (data) {
                    data = JSON.parse(data);
                    $.each(data,function(index,item){
                        new_current_view = "'.api_get_path(WEB_IMG_PATH).'" + item.view;
                        //eyes
                        $("#linktool_"+item.id).attr("class", item.fclass);
                        //tool
                        var $toolImage = $("#toolimage_" + item.id);

                        if (!$toolImage.data("forced-src")) {
                            $toolImage.attr("src", item.image);
                        }

                        //class
                        $("#tooldesc_" + item.id).attr("class", item.tclass);
                        $("#istooldesc_" + item.id).attr("class", item.tclass);
                    });
                    $(".show-hide-all-tools").removeClass("disabled");
                    $(".normal-message").hide();
                    $("#id_confirmation_message").html(message_invisible);
                    $("#id_confirmation_message").show();
                    buttonForAllShowHide();
                },
                error: function( jqXHR, textStatus, errorThrown ) {
                    $(".show-hide-all-tools").removeClass("disabled");
                    $(".normal-message").hide();
                    buttonForAllShowHide();
                }
            });
        });

        $("#thematic-show").click(function(){
            $(".btn-hide-thematic").hide();
            $(".btn-show-thematic").show(); //show using class
            $("#pross").fadeToggle(); //Not working collapse for Chrome
        });

        $("#thematic-hide").click(function(){
            $(".btn-show-thematic").hide(); //show using class
            $(".btn-hide-thematic").show();
            $("#pross").fadeToggle(); //Not working collapse for Chrome
        });

        $(".make_visible_and_invisible").attr("href", "javascript:void(0);");
        $(".make_visible_and_invisible > em").click(function () {
            make_visible = "visible.gif";
            make_invisible = "invisible.gif";
            //path_name = $(this).attr("src");
            //list_path_name = path_name.split("/");
            //image_link = list_path_name[list_path_name.length - 1];
            tool_id = $(this).attr("id");
            tool_info = tool_id.split("_");
            my_tool_id = tool_info[1];
            $("#id_normal_message").attr("class", "normal-message alert alert-success");

            $.ajax({
                contentType: "application/x-www-form-urlencoded",
                beforeSend: function(myObject) {
                    $(".normal-message").show();
                    $("#id_confirmation_message").hide();
                },
                type: "GET",
                url: "'.api_get_path(WEB_AJAX_PATH).'course_home.ajax.php?'.api_get_cidreq().'&a=set_visibility",
                data: "id=" + my_tool_id + "&sent_http_request=1",
                success: function(data) {
                    eval("var info=" + data);
                    new_current_tool_image = info.image;
                    new_current_view = "'.api_get_path(WEB_IMG_PATH).'" + info.view;
                    //eyes
                    //$("#" + tool_id).attr("src", new_current_view);
                     $("#linktool_"+my_tool_id).attr("class", info.fclass);
                     $("#linktool_"+my_tool_id).attr("title", info.label);

                    //tool
                    var $toolImage = $("#toolimage_" + my_tool_id);

                    if (!$toolImage.data("forced-src")) {
                        $toolImage.attr("src", new_current_tool_image);
                    }

                    //clase
                    $("#tooldesc_" + my_tool_id).attr("class", info.tclass);
                    $("#istooldesc_" + my_tool_id).attr("class", info.tclass);

                    if (info.message == "is_active") {
                        message = "'.get_lang('ToolIsNowVisible').'";
                        $("#" + tool_id)
                        .attr("alt", "'.get_lang('Deactivate').'")
                        .attr("title", "'.get_lang('Deactivate').'");
                    } else {
                        message = "'.get_lang('ToolIsNowHidden').'";
                        $("#" + tool_id)
                        .attr("alt", "'.get_lang('Activate').'")
                        .attr("title", "'.get_lang('Activate').'");
                    }
                    $(".normal-message").hide();
                    $("#id_confirmation_message").html(message);
                    $("#id_confirmation_message").show();
                }
            });
        });
    });
</script>';

// The section for the tabs
$this_section = SECTION_COURSES;

$user_id = api_get_user_id();
$course_code = api_get_course_id();
$courseId = api_get_course_int_id();
$sessionId = api_get_session_id();
$show_message = '';

if (api_is_invitee()) {
    $isInASession = $sessionId > 0;
    $isSubscribed = CourseManager::is_user_subscribed_in_course(
        $user_id,
        $course_code,
        $isInASession,
        $sessionId
    );

    if (!$isSubscribed) {
        api_not_allowed(true);
    }
}

// Deleting group session
Session::erase('toolgroup');
Session::erase('_gid');

$isSpecialCourse = CourseManager::isSpecialCourse($courseId);

if ($isSpecialCourse) {
    if (isset($_GET['autoreg']) && $_GET['autoreg'] == 1) {
        if (CourseManager::subscribeUser($user_id, $course_code, STUDENT)) {
            Session::write('is_allowed_in_course', true);
        }
    }
}

$action = !empty($_GET['action']) ? Security::remove_XSS($_GET['action']) : '';

if ($action === 'subscribe' && Security::check_token('get')) {
    Security::clear_token();
    $generateRedirectUrlAfterSubscription = function () use ($course_code, $courseId, $user_id) {
        $redirectionTarget = api_get_self();
        if (CourseManager::autoSubscribeToCourse($course_code)) {
            if (CourseManager::is_user_subscribed_in_course($user_id, $course_code)) {
                Session::write('is_allowed_in_course', true);
            }
            if (api_get_configuration_value('catalog_course_subscription_in_user_s_session')) {
                $user = api_get_user_entity(api_get_user_id());

                if ($user && $accesibleSessions = $user->getCurrentlyAccessibleSessions()) {
                    return api_get_self().'?id_session='.$accesibleSessions[0]->getId();
                }
            }
        } elseif (api_get_configuration_value('catalog_course_subscription_in_user_s_session')) {
            $user = api_get_user_entity(api_get_user_id());

            if ($user && !$user->getCurrentlyAccessibleSessions()) {
                // subscription was probably refused because user session expired, go back to page "about"
                return api_get_path(WEB_PATH).'course/'.$courseId.'/about';
            }
        }

        return $redirectionTarget;
    };

    header('Location: '.$generateRedirectUrlAfterSubscription());
    exit;
}

/*	Is the user allowed here? */
api_protect_course_script(true);

/*  STATISTICS */
if (!isset($coursesAlreadyVisited[$course_code])) {
    Event::accessCourse();
    $coursesAlreadyVisited[$course_code] = 1;
    Session::write('coursesAlreadyVisited', $coursesAlreadyVisited);
}

$logInfo = [
    'tool' => 'course-main',
    'tool_id' => 0,
    'tool_id_detail' => 0,
    'action' => $action,
    'info' => '',
];
Event::registerLog($logInfo);

/* Auto launch code */
$autoLaunchWarning = '';
$showAutoLaunchLpWarning = false;
$course_id = api_get_course_int_id();
$lpAutoLaunch = api_get_course_setting('enable_lp_auto_launch');
$session_id = api_get_session_id();
$allowAutoLaunchForCourseAdmins = api_is_platform_admin() || api_is_allowed_to_edit(true, true) || api_is_coach();

if (!empty($lpAutoLaunch)) {
    if ($lpAutoLaunch == 2) {
        // LP list
        if ($allowAutoLaunchForCourseAdmins) {
            $showAutoLaunchLpWarning = true;
        } else {
            $session_key = 'lp_autolaunch_'.$session_id.'_'.api_get_course_int_id().'_'.api_get_user_id();
            if (!isset($_SESSION[$session_key])) {
                // Redirecting to the LP
                $url = api_get_path(WEB_CODE_PATH).'lp/lp_controller.php?'.api_get_cidreq().'&id_session='.$session_id;
                $_SESSION[$session_key] = true;
                header("Location: $url");
                exit;
            }
        }
    } else {
        $lp_table = Database::get_course_table(TABLE_LP_MAIN);
        $condition = '';
        if (!empty($session_id)) {
            $condition = api_get_session_condition($session_id);
            $sql = "SELECT id FROM $lp_table
                    WHERE c_id = $course_id AND autolaunch = 1 $condition
                    LIMIT 1";
            $result = Database::query($sql);
            // If we found nothing in the session we just called the session_id =  0 autolaunch
            if (Database::num_rows($result) == 0) {
                $condition = '';
            }
        }

        $sql = "SELECT id FROM $lp_table
                WHERE c_id = $course_id AND autolaunch = 1 $condition
                LIMIT 1";
        $result = Database::query($sql);
        if (Database::num_rows($result) > 0) {
            $lp_data = Database::fetch_array($result, 'ASSOC');
            if (!empty($lp_data['id'])) {
                if ($allowAutoLaunchForCourseAdmins) {
                    $showAutoLaunchLpWarning = true;
                } else {
                    $session_key = 'lp_autolaunch_'.$session_id.'_'.api_get_course_int_id().'_'.api_get_user_id();
                    if (!isset($_SESSION[$session_key])) {
                        // Redirecting to the LP
                        $url = api_get_path(WEB_CODE_PATH).'lp/lp_controller.php?'.api_get_cidreq().'&action=view&lp_id='.$lp_data['id'];

                        $_SESSION[$session_key] = true;
                        header("Location: $url");
                        exit;
                    }
                }
            }
        }
    }
}

if ($showAutoLaunchLpWarning) {
    $autoLaunchWarning = get_lang('TheLPAutoLaunchSettingIsONStudentsWillBeRedirectToAnSpecificLP');
}

$forumAutoLaunch = api_get_course_setting('enable_forum_auto_launch');
if ($forumAutoLaunch == 1) {
    if ($allowAutoLaunchForCourseAdmins) {
        if (empty($autoLaunchWarning)) {
            $autoLaunchWarning = get_lang('TheForumAutoLaunchSettingIsOnStudentsWillBeRedirectToTheForumTool');
        }
    } else {
        $url = api_get_path(WEB_CODE_PATH).'forum/index.php?'.api_get_cidreq().'&id_session='.$session_id;
        header("Location: $url");
        exit;
    }
}

if (api_get_configuration_value('allow_exercise_auto_launch')) {
    $exerciseAutoLaunch = (int) api_get_course_setting('enable_exercise_auto_launch');
    if ($exerciseAutoLaunch == 2) {
        if ($allowAutoLaunchForCourseAdmins) {
            if (empty($autoLaunchWarning)) {
                $autoLaunchWarning = get_lang(
                    'TheExerciseAutoLaunchSettingIsONStudentsWillBeRedirectToTheExerciseList'
                );
            }
        } else {
            // Redirecting to the document
            $url = api_get_path(WEB_CODE_PATH).'exercise/exercise.php?'.api_get_cidreq().'&id_session='.$session_id;
            header("Location: $url");
            exit;
        }
    } elseif ($exerciseAutoLaunch == 1) {
        if ($allowAutoLaunchForCourseAdmins) {
            if (empty($autoLaunchWarning)) {
                $autoLaunchWarning = get_lang(
                    'TheExerciseAutoLaunchSettingIsONStudentsWillBeRedirectToAnSpecificExercise'
                );
            }
        } else {
            // Redirecting to an exercise
            $table = Database::get_course_table(TABLE_QUIZ_TEST);
            $condition = '';
            if (!empty($session_id)) {
                $condition = api_get_session_condition($session_id);
                $sql = "SELECT iid FROM $table
                        WHERE c_id = $course_id AND autolaunch = 1 $condition
                        LIMIT 1";
                $result = Database::query($sql);
                // If we found nothing in the session we just called the session_id = 0 autolaunch
                if (Database::num_rows($result) == 0) {
                    $condition = '';
                }
            }

            $sql = "SELECT iid FROM $table
                    WHERE c_id = $course_id AND autolaunch = 1 $condition
                    LIMIT 1";
            $result = Database::query($sql);
            if (Database::num_rows($result) > 0) {
                $row = Database::fetch_array($result, 'ASSOC');
                $exerciseId = $row['iid'];
                $url = api_get_path(WEB_CODE_PATH).
                    'exercise/overview.php?exerciseId='.$exerciseId.'&'.api_get_cidreq().'&id_session='.$session_id;
                header("Location: $url");
                exit;
            }
        }
    }
}

$documentAutoLaunch = api_get_course_setting('enable_document_auto_launch');
if ($documentAutoLaunch == 1) {
    if ($allowAutoLaunchForCourseAdmins) {
        if (empty($autoLaunchWarning)) {
            $autoLaunchWarning = get_lang('TheDocumentAutoLaunchSettingIsOnStudentsWillBeRedirectToTheDocumentTool');
        }
    } else {
        // Redirecting to the document
        $url = api_get_path(WEB_CODE_PATH).'document/document.php?'.api_get_cidreq().'&id_session='.$session_id;
        header("Location: $url");
        exit;
    }
}

// Used in different pages
$tool_table = Database::get_course_table(TABLE_TOOL_LIST);

/*	Introduction section (editable by course admins) */
$content = Display::return_introduction_section(
    TOOL_COURSE_HOMEPAGE,
    [
        'CreateDocumentWebDir' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/',
        'CreateDocumentDir' => 'document/',
        'BaseHref' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/',
    ]
);

/*	SWITCH TO A DIFFERENT HOMEPAGE VIEW
    the setting homepage_view is adjustable through
    the platform administration section */
if (!empty($autoLaunchWarning)) {
    $show_message .= Display::return_message(
        $autoLaunchWarning,
        'warning'
    );
}

$homePageView = api_get_setting('homepage_view');

switch ($homePageView) {
    case 'activity':
    case 'activity_big':
        require 'activity.php';
        break;
    case '2column':
        require '2column.php';
        break;
    case '3column':
        require '3column.php';
        break;
    case 'vertical_activity':
        require 'vertical_activity.php';
        break;
}

// Get session-career diagram
$diagram = '';
$allow = api_get_configuration_value('allow_career_diagram');
if ($allow === true) {
    $htmlHeadXtra[] = api_get_js('jsplumb2.js');
    $extra = new ExtraFieldValue('session');
    $value = $extra->get_values_by_handler_and_field_variable(
        api_get_session_id(),
        'external_career_id'
    );

    if (!empty($value) && isset($value['value'])) {
        $careerId = $value['value'];
        $extraFieldValue = new ExtraFieldValue('career');
        $item = $extraFieldValue->get_item_id_from_field_variable_and_field_value(
            'external_career_id',
            $careerId,
            false,
            false,
            false
        );

        if (!empty($item) && isset($item['item_id'])) {
            $careerId = $item['item_id'];
            $career = new Career();
            $careerInfo = $career->get($careerId);
            if (!empty($careerInfo)) {
                $extraFieldValue = new ExtraFieldValue('career');
                $item = $extraFieldValue->get_values_by_handler_and_field_variable(
                    $careerId,
                    'career_diagram',
                    false,
                    false,
                    false
                );

                if (!empty($item) && isset($item['value']) && !empty($item['value'])) {
                    /** @var Graph $graph */
                    $graph = UnserializeApi::unserialize(
                        'career',
                        $item['value']
                    );
                    $diagram = Career::renderDiagram($careerInfo, $graph);
                }
            }
        }
    }
}


$showCourseTimeCounterOnSessions = api_get_configuration_value('course_home_show_time_counter');
$showCourseTimeCounterOnThisSession = SessionManager::getFilteredExtraFields($sessionId,["show_time_counter_on_course"]);

$showCourseTimeSpent = false;

if (!empty($showCourseTimeCounterOnThisSession) && $showCourseTimeCounterOnThisSession[0]['value']) {
    $showCourseTimeSpent = true;
}

$contentCounter = '';

if ($sessionId != 0 && $showCourseTimeCounterOnSessions && $showCourseTimeSpent) {
    $timeSpentOnCourse = Tracking::get_time_spent_on_the_course($user_id, $courseId, $sessionId);
    $hours = gmdate("H",$timeSpentOnCourse);
    $minutes = gmdate("i",$timeSpentOnCourse);

    $userInfo = api_get_user_info($user_id);
    $firstName = $userInfo['firstname'];


    $contentCounter = '<style>
        .button-counter-container {
            display: inline-block;
            background: linear-gradient(to bottom, #c4d82e 0%,#5b8b3e 100%);
            color: white;
            padding: 5px 10px;
            margin: 2px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button-counter-container:hover {
            background-color: #007a8c;
        }

        .counter-container {
            display: none;
            position: absolute;
            background: linear-gradient(to bottom, #c4d82e 0%,#5b8b3e 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            white-space: nowrap;
            margin-top: 1px;
        }

        .counter-container.show {
            display: block;
            animation: slideDown 0.3s ease-in-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>';

    $htmlHeadXtra[] = '<script>
        $(document).ready(function() {

            var ctaHtml = `
                <div id="view_as_link" class="pull-right">
                    <div class="button-counter-container" id="container-counter-button">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <div id="container-course-counter" class="counter-container">
                        <div id="course-counter" class="cta-content">
                        </div>
                    </div>
                </div>
            `;

            var targetElement = $("#view_as_link");
    
            if (targetElement.length === 0) {
                targetElement = $(".breadcrumb").first();
            }
    
            if (targetElement.length > 0) {
                targetElement.before(ctaHtml);
            }

            $("#container-counter-button").hover(
                function() {
                    OpenCourseHomeCounter();
                },
                function() {
                }
            );

            OpenCourseHomeCounter(10000);
        });
        
        function OpenCourseHomeCounter(timeToWait = 10000) {
            $.ajax({
                type: "GET",
                url: "'.api_get_path(WEB_AJAX_PATH).'course_home.ajax.php?'.api_get_cidreq().'&a=get_counter&course_id='.$courseId.'&session_id='.$sessionId.'",
                success: function (data) {
                    $("#course-counter").html(data);
                    
                    $("#container-course-counter").css("display", "block");
    
                    var contentWidth = $("#container-course-counter").outerWidth();
                    var buttonWidth = $("#container-counter-button").outerWidth();
    
                    var leftPosition = $("#container-counter-button").offset().left + (buttonWidth) - (contentWidth);
    
                    $("#container-course-counter").css({
                        "left": leftPosition + "px",
                        "top": ($("#container-counter-button").offset().top + $("#container-counter-button").outerHeight()) + "px"
                    });
    
                    $("#container-course-counter").addClass("show");
        
                    setTimeout(function() {
                        $("#container-course-counter").removeClass("show");
                        $("#container-course-counter").css("display", "none");
                    }, timeToWait);
                },
                error: function() {
                    console.log("Error updating counter ");
                }
            });
        }
    </script>'; 
    }

$content = $contentCounter.'<div id="course_tools">'.$diagram.$content.'</div>';

// Deleting the objects
Session::erase('_gid');
Session::erase('oLP');
Session::erase('lpobject');
api_remove_in_gradebook();
Exercise::cleanSessionVariables();
DocumentManager::removeGeneratedAudioTempFile();

$tpl = new Template(null);
$tpl->assign('message', $show_message);
$tpl->assign('content', $content);

// Direct login to course
$tpl->assign('course_code', $course_code);
$tpl->display_one_col_template();
