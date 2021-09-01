<?php

use Chamilo\CoreBundle\Entity\Course;
use Chamilo\CoreBundle\Entity\Session;

$cidReset = true;
require_once 'main/inc/global.inc.php';

api_block_anonymous_users();

$codePath = api_get_path(WEB_CODE_PATH);
$coursePath = api_get_path(WEB_COURSE_PATH);
$pluginPath = api_get_path(WEB_PLUGIN_PATH);
$toolName = get_lang('Dashboard');

$userId = api_get_user_id();
$controller = new IndexManager(get_lang('MyCourses'));
$courseAndSessions = $controller->returnCoursesAndSessionsViewBySession($userId, true);
$ajax_url = api_get_path(WEB_AJAX_PATH).'course.ajax.php?a=add_course_vote';
/*
echo "<pre>";
echo var_dump($courseAndSessions);
echo "</pre>";
*/
//exit;

$htmlHeadXtra[] = '
    <style>
        .menu-item-gestor {

        }
        .menu-item-gestor a {
            color: #337ab7;
        }
        .panel-body {
            padding: 10px;
        }

        .item-name {
            font-weight: bold;
            font-size: 16px;
            margin-left: 5px;
            vertical-align: super;
        }
        .ui-tabs .ui-tabs-nav li.ui-tabs-active {
            background: #FFFFFF !important;
        }
        .ui-tabs .ui-state-default, .ui-tabs .ui-widget-content .ui-state-default, .ui-tabs .ui-widget-header .ui-state-default {
            background: #F0F0F0 !important;
        }
        .ui-state-active a, .ui-state-active a:link, .ui-state-active a:visited{
            font-weight: bold;
        }
    </style>';

$htmlHeadXtra[] = '<script>
    $(function() {
        var height_max = 0;
        $(".box-session-active").each(function(index) {
            var height_div = parseInt($(this).height());
            if (height_div > height_max) {
                height_max = height_div;
            }
        });
        $(".box-session-active").height(height_max);

        
        var height_max = 0;
        $(".box-session-future").each(function(index) {
            var height_div = parseInt($(this).height());
            if (height_div > height_max) {
                height_max = height_div;
            }
        });
        $(".box-session-future").height(height_max);
    });
    </script>';

//Display::display_header($toolName);

$content = '';

// contenido tabs
$content .= '<script>
            $(function(){
                $("#tabs").tabs();
            });
        </script>';

$content .= '<div id="tabs">';
$content .= '<ul>';
$listActivos = [];
$listFuturos = [];
$listPasados = [];

$categories = SessionManager::get_all_session_category();
$orderedCategories = [];
if (!empty($categories)) {
    foreach ($categories as $category) {
        $orderedCategories[$category['id']] = $category['name'];
    }
}

foreach ($courseAndSessions['sessions'] as $key => $sessionCategory) {
    foreach ($sessionCategory['sessions'] as $sessionItem) {
        $groupInfo = [
            'id' => $sessionItem['session_id'],
            'session_category_id' => $key ? $key : null,
            'session_category_name' => $sessionCategory['session_category']['name'],
            'name' => $sessionItem['session_name'],
            'access_start_date' => $sessionItem['access_start_date']
            ? $sessionItem['access_start_date']
            : null,
            'access_end_date' => $sessionItem['access_end_date']
            ? $sessionItem['access_end_date']
            : null,
            'coach_access_start_date' => $sessionItem['coach_access_start_date']
            ? $sessionItem['coach_access_start_date']
            : null,
            'coach_access_end_date' => $sessionItem['coach_access_end_date']
            ? $sessionItem['coach_access_end_date']
            : null,
            'courses' => $sessionItem['courses'],
        ];

        $sessionId = $groupInfo['id'];
        $accessStartDate = $groupInfo['access_start_date'];
        $accessEndDate = $groupInfo['access_end_date'];

        if (!empty($accessStartDate) && strtotime(api_get_local_time($accessStartDate)) > time()) {
            // futuro curso
            $listFuturos[$sessionId] = $groupInfo;
            continue;
        }

        if (!empty($accessEndDate) && strtotime(api_get_local_time($accessEndDate)) < time()) {
            $listPasados[$sessionId] = $groupInfo;
            continue;
        }

        //$coachList = CourseManager::get_coachs_from_course_to_string($row['session_id'], $infoCourse['id']);
        //$groupInfo['coach'] = $coachList;
        $listActivos[$sessionId] = $groupInfo;
    }
}

$iconSetting = Display::return_icon(
    'session.png',
    get_lang('Session'),
    [],
    ICON_SIZE_MEDIUM
);

$content .= '<li><a href="#tabs-1">'.get_lang('ActiveGroups').'</a></li>';
//$content .= '<li><a href="#tabs-2">Cursos futuros</a></li>';
if (count($listPasados) > 0) {
    $content .= '<li><a href="#tabs-3">'.get_lang('GroupsCompleted').'</a></li>';
}
$content .= '</ul>';

$content .= '<div id="tabs-1">';
if (empty($listActivos)) {
    $content .= Display::return_message("No hay curso activos", 'warning', true);
}
$content .= '<div class="row">';
$content .= '<div id="list-hot-courses" class="grid-courses">'; //$content .= '<div class="col-md-12">';
if (count($listActivos) > 0) {
    //$content .= '<table class="table data_table">';

    foreach ($listActivos as $key => $value) {
        $sessionId = $key;
        $sessionInfo = api_get_session_info($sessionId);
        $session = api_get_session_entity($sessionId);

        foreach ($value['courses'] as $courseItem) {
            $course = api_get_course_entity($courseItem['real_id']);
            $course_info = api_get_course_info($course->getCode());
            $imagePath = $course_info['course_image_large']; //CourseManager::getPicturePath($course, true);

            $content .= '<div class="col-xs-12 col-sm-6 col-md-4">';
            $content .= '<div class="items items-hotcourse">';
            $content .= '<div class="image">';
            $content .= '<a title="'.htmlspecialchars($value['name']).'" href="'.$coursePath.$course->getCode().'/index.php?id_session='.$sessionId.'">';
            $content .= '<img src="'.$imagePath.'" class="img-responsive" alt="'.htmlspecialchars($value['name']).'">';
            $content .= '</a>';

            if (!empty($value['session_category_id'])) {
                $content .= '<span class="category">'.$orderedCategories[$value['session_category_id']].'</span>';
                $content .= '<div class="cribbon"></div>';
            }

            $content .= '<div class="user-actions">'.CourseManager::returnDescriptionButton($course_info).'</div>';
            $content .= '</div>';
            $content .= '<div class="description">';
            $content .= '<div class="block-title">';
            $content .= '<h5 class="title">';
            $content .= '<a title="'.htmlspecialchars($value['name']).'" href="'.$coursePath.$course->getCode().'/index.php?id_session='.$sessionId.'">';
            $content .= htmlspecialchars($course->getTitle());
            $content .= '</a>';
            $content .= '</h5>';
            $content .= '<h5><em>'.htmlspecialchars($value['name']).'</em></h5>';
            $content .= '</div>';

            if (api_get_configuration_value('hide_course_rating') === false) {
                $point_info = CourseManager::get_course_ranking($course_info['real_id'], 0);
                $rating_html = Display::return_rating_system(
                    'star_'.$course_info['real_id'],
                    $ajax_url.'&course_id='.$course_info['real_id'],
                    $point_info
                );
                $content .= '<div class="ranking">';
                $content .= $rating_html;
                $content .= '</div>';
            }
            /*

            $content .= '<div class="toolbar row">';
            $content .= '<div class="col-sm-4">';
            if (item.price) {
                $content .= '{{ item.price }}';
            }
            $content .= '</div>';
            $content .= '<div class="col-sm-8">';
            $content .= '<div class="btn-group" role="group">';
            $content .= '{{ item.register_button }}';
            $content .= '{{ item.unsubscribe_button }}';
            $content .= '</div>';
            $content .= '</div>';
            $content .= '</div>';
            */
            $content .= '</div>';
            $content .= '</div>';
            $content .= '</div>';
            /*
            $content .= '<tr>';
            // Course image
            $content .= '<td>';
            $content .= '<img src="'.$imagePath.'" />';
            $content .= '</td>';

            // Session name and course title
            $content .= '<td>';
            $content .= '<a title="'.htmlspecialchars($value['name']).'" href="'.$codePath.'session/index.php?session_id='.$sessionId.'">';
            $content .= '<span style="font-weight: bold; font-size: 16px;">'.htmlspecialchars($value['name']).'</span>';
            $content .= '</a>';
            $content .= '<br><span style="font-style: italic; font-size: 14px;">'.htmlspecialchars($course->getTitle()).'</span>';
            if (!empty($value['session_category_id'])) {
                $content .= '<span class="btn btn-primary btn-xs pull-right">';
                $content .= $orderedCategories[$value['session_category_id']];
                $content .= '</span>';
            }
            $content .= '</td>';

            // Dates
            $content .= '<td style="font-size:14px; ">';
            $newDates = SessionManager::convert_dates_to_local($sessionInfo, false);
            $content .= date("d/m/Y", strtotime($newDates['access_start_date'])).'<br>';
            if (!empty($newDates['access_end_date'])) {
                $content .= date("d/m/Y", strtotime($newDates['access_end_date']));
            }
            $content .= '</td>';

            // Coach
            $namesOfCoaches = [];
            $coachSubscriptions = $session->getUserCourseSubscriptionsByStatus($course, Session::COACH);

            if ($coachSubscriptions) {

                foreach ($coachSubscriptions as $subscription) {
                    $namesOfCoaches[] = $subscription->getUser()->getCompleteNameWithUserName();
                }
            }
            $coachHtml = ($namesOfCoaches ? implode('<br>', $namesOfCoaches) : 'Sin tutores');

            $content .= '<td style="vertical-align:middle; font-size:14px; text-align:center" class="text-primary">';
            $content .= $coachHtml;
            $content .= '</td>';


            // Acciones
            $content .= '<td class="text-right" style="vertical-align:middle">';
            $actions = Display::url(
                Display::return_icon('2rightarrow.png', get_lang('Details')),
                $coursePath.$course->getCode()."/index.php?id_session=$sessionId"
            );
            $content .= $actions;
            $content .= '</td>';

            $content .= '</tr>';
            */
        }
    }
    //$content .= '</table>';
}
$content .= '</div>';

$sessionId = 0;

$content .= '<div class="col-md-12 col-lg-12">';
$content .= '<div class="panel panel-default">';
$content .= '<div class="session panel-body box-couse-active">';
$content .= '<div class="row">';
$content .= '<div class="col-md-12">';

$content .= '<div class="sessions-items">';

    if (count($courseAndSessions['courses']) > 0) {
        $content .= '<table class="table" style="margin-bottom:0px">';
        $content .= '<tr><th colspan="3">'.get_lang('Courses').'</th></tr>';
        foreach ($courseAndSessions['courses'] as $key => $courseItem) {
            $courseUrl = $courseItem['course']['course_public_url'];
            $courseId = $courseItem['course']['real_id'];
            $content .= '<tr>';
            $content .= '<td>';
            $content .= Display::url(
                '<img src="'.$courseItem['course']['course_image'].'" /> ',
                $courseUrl
                );
            $content .= '</td>';
            $content .= '<td style="vertical-align: middle;">';
            $content .= Display::url(
                $courseItem['course']['title'],
                $courseUrl
            );
            $content .= '</td>';
            $content .= '<td class="text-right" style="vertical-align: middle;">';

            $allowZoom = api_get_plugin_setting('zoom', 'tool_enable') === 'true';
            if ($allowZoom) {
                $content .= Display::url(
                    Display::return_icon('zoom_meet.png', 'Videoconferencia Zoom'),
                    $pluginPath."zoom/start.php?id_session=$sessionId&cidReq=".$courseItem['code']
                ).' ';
            }

            $course_info = api_get_course_info_by_id($courseId);

            $userInCourseStatus = CourseManager::getUserInCourseStatus($userId, $course_info['real_id']);
            $course_info['status'] = empty($sessionId) ? $userInCourseStatus : STUDENT;
            $course_info['id_session'] = $sessionId;

            // For each course, get if there is any notification icon to show
            // (something that would have changed since the user's last visit).
            $show_notification = !api_get_configuration_value('hide_course_notification')
            ? Display::show_notification($course_info)
            : '';
            $content .= $show_notification;

            $content .= '</td>';
            $content .= '</tr>';
        }
        $content .= '</table>';
    }
    $content .= '</div>';

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

$content .= '</div>';
$content .= '</div>';
/*
$content .= '<div id="tabs-2">';
if (empty($listFuturos)) {
    $content .= Display::return_message("No hay curso futuros", 'warning', true);
}
$content .= '<div class="row">';
foreach ($listFuturos as $key => $value) {
    $sessionId = $key;
    $sessionInfo = api_get_session_info($sessionId);
    $content .= '<div class="col-md-12 col-lg-6">';
    $content .= '<div class="panel panel-default">';
    $content .= '<div class="session panel-body box-session-future">';
    $content .= '<div class="row">';
    $content .= '<div class="col-md-12">';
    $content .= '<ul class="info-session list-inline" style="margin-bottom:0;">';
    $content .= '<li>';
    $dateSessionParse = SessionManager::parseSessionDates($sessionInfo, true);
    $content .= '<i class="fa fa-calendar" aria-hidden="true"></i> '.$dateSessionParse['access'];
    $content .= '</li>';
    $content .= '</ul>';
    $content .= '<div class="sessions-items">';
    $content .= '<div style="height:40px; overflow: hidden;">';
    $content .= '<h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">';
    $content .= '<a title="'.htmlspecialchars($value['name']).'" href="#">'.htmlspecialchars($value['name']).'</a>&nbsp;';
    $content .= '</h4>';
    $content .= '</div>';

    if (count($value['courses']) === 0) {
        $content .= '<div class="alert alert-warning">'.get_lang('NoCoursesForThisSession').'</div>';
    } else {
        $content .= '<table class="table" style="margin-bottom:0px">';
        foreach ($value['courses'] as $course) {
            $courseUrl = "#"; // api_get_course_url($course['course_code'], $sessionId);
            $courseId = $course['real_id'];

            $content .= '<tr>';
            $content .= '<td>';
            $content .= Display::url(
                $course['title'],
                $courseUrl
            );
            $content .= '</td>';

            $content .= '</tr>';
        } // foreach course
        $content .= '</table>';
    }

    $content .= '</div>';

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';
}
$content .= '</div>';
$content .= '</div>';
*/
if (!empty($listPasados)) {
    $content .= '<div id="tabs-3">';

    $content .= '<div class="row">';
    if (count($listPasados) > 0) {
        $content .= '<table class="data_table table">';
        $content .= '<tr><th>Curso</th><th>Opciones</th></tr>';
        foreach ($listPasados as $key => $value) {
            $sessionId = $key;
            $sessionInfo = api_get_session_info($sessionId);

            if ($sessionInfo['visibility'] == SESSION_INVISIBLE) {
                continue;
            }
            $content .= '<tr>';
            $content .= '<td><h4><a href="'.$codePath.'session/index.php?session_id='.$sessionId.'">'.htmlspecialchars($value['name']).'</a></h4>';
            $dateSessionParse = SessionManager::parseSessionDates($sessionInfo, true);
            $content .= '<i class="fa fa-calendar" aria-hidden="true"></i> '.$dateSessionParse['access'];
            $content .= '</td>';

            $tools = Display::url(
                $iconSetting,
                $codePath.'session/index.php?session_id='.$key
            );
            $content .= '<td style="vertical-align:middle; min-width:120px;">';
            $content .= $tools;
            $content .= '</td>';
            $content .= '</tr>';
        }
        $content .= '</table>';
    }
    $content .= '</div>';
    $content .= '</div>';
}

//Display::display_footer();

// Block Menu
// Block Menu
$viewQuickAccessMenu = api_get_configuration_value('view_quick_access_menu');
$menu = $viewQuickAccessMenu
    ? SocialManager::show_quick_access_menu('home')
    : SocialManager::show_social_menu('home');

$tpl = new Template($toolName);
SocialManager::setSocialUserBlock($tpl, $userId, 'home');
$tpl->assign('social_menu_block', $menu);
$tpl->assign('help_block', $controller->return_help());
$tpl->assign('content', $content);
/*
$tpl->assign('add_post_form', $wallSocialAddPost);
$tpl->assign('posts', $posts);
$tpl->assign('social_auto_extend_link', $socialAutoExtendLink);
$tpl->assign('search_friends_form', $formSearch->returnForm());
$tpl->assign('social_friend_block', $friend_html);
$tpl->assign('social_search_block', $social_search_block);
$tpl->assign('social_skill_block', SocialManager::getSkillBlock($user_id, 'vertical'));
$tpl->assign('social_group_block', $social_group_block);
$tpl->assign('session_list', null);
*/
$dashboardStudentLayout = $tpl->get_template('social/dashboard_student.tpl');
$tpl->display($dashboardStudentLayout);
