<?php

$cidReset = true;
require_once 'main/inc/global.inc.php';

api_block_anonymous_users();

if (!api_is_teacher()) {
    api_not_allowed(true);
}

$sessionId = isset($_REQUEST['id_session']) ? (int) $_REQUEST['id_session'] : 0;
$keyWord = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';

$queryString = 'id_session='.intval($_REQUEST['id_session']);

$codePath = api_get_path(WEB_CODE_PATH);
$coursePath = api_get_path(WEB_COURSE_PATH);
$pluginPath = api_get_path(WEB_PLUGIN_PATH);
$toolName = get_lang('Students');
$userId = api_get_user_id();
$controller = new IndexManager(get_lang('MyCourses'));
$courseAndSessions = $controller->returnCoursesAndSessionsViewBySession($userId, true);

$htmlHeadXtra[] = '
    <style>
        .menu-item-gestor {
        
        }
        .menu-item-gestor a {
            color: #660000;
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
    </style>';

$user_table = Database::get_main_table(TABLE_MAIN_USER);
$session_table = Database::get_main_table(TABLE_MAIN_SESSION);
$session_rel_course_rel_user_table = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$sessionList = [];

$sql = "SELECT DISTINCT session_id FROM $session_rel_course_rel_user_table WHERE user_id='".$userId."' AND status = 2";

if (!empty($sessionId)) {
    $sql = "SELECT DISTINCT session_id FROM $session_rel_course_rel_user_table WHERE session_id = $sessionId AND user_id='".$userId."' AND status = 2";
}

$result = Database::query($sql);
while ($row = Database::fetch_assoc($result)) {
    $sessionList[] = $row['session_id'];
}

Display::display_header($toolName);

echo '<div class="row">';
echo '<div class="col-xs-12 breadcrumb" style="padding: 10px 20px 0px 20px; border-radius: 10px;">';

// Icon 1
echo '<div class="menu-item-gestor col-md-4 col-sm-4 col-xs-12">';
echo '<div class="panel panel-default" style="margin-bottom:10px">';
echo '<div class="session panel-body">';
$icon = Display::return_icon(
    'session.png',
    get_lang('Groups'),
    ["style" => "margin-right:5px; vertical-align: text-bottom;"],
    ICON_SIZE_MEDIUM
);
$tools = Display::url(
    $icon.' <span class="item-name">'.get_lang('Groups').'</span>',
    'teacher_groups.php'
);
echo $tools;
echo '</div>';
echo '</div>';
echo '</div>';

// Icon 2
echo '<div class="menu-item-gestor col-md-4 col-sm-4 col-xs-12">';
echo '<div class="panel panel-default" style="margin-bottom:10px">';
echo '<div class="session panel-body">';
$icon = Display::return_icon(
    'user.png',
    get_lang('Students'),
    ["style" => "margin-right:5px; vertical-align: text-bottom;"],
    ICON_SIZE_MEDIUM
);
$tools = Display::url(
    $icon.' <span class="item-name">'.get_lang('Students').'</span>',
    'teacher_student_list.php'
);
echo $tools;
echo '</div>';
echo '</div>';
echo '</div>';

// Icon 3
echo '<div class="menu-item-gestor col-md-4 col-sm-4 col-xs-12">';
echo '<div class="panel panel-default" style="margin-bottom:10px">';
echo '<div class="session panel-body">';
$icon = Display::return_icon(
    'zoom_meet.png',
    ZoomPlugin::create()->get_lang('ZoomVideoConferences'),
    ["style" => "margin-right:5px; vertical-align: text-bottom;"],
    ICON_SIZE_MEDIUM
);
$tools = Display::url(
    $icon.' <span class="item-name">'.ZoomPlugin::create()->get_lang('ZoomVideoConferences').'</span>',
    'teacher_videconferences_list.php'
);
echo $tools;
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="clearfix"></div>';
echo '</div>';

echo '</div>';

echo '
<div id="toolbarUser" class="actions">
    <div class="row">
        <div class="col-sm-4">
            <form class="form-inline" action="teacher_student_list.php" method="get" name="search_simple"
                id="search_simple">
                <fieldset>
                    <div class="form-group ">
                        <label for="search_simple_keyword">
                            Buscar
                        </label>
                        <input aria-label="Buscar usuarios" class=" form-control" name="keyword" type="text"
                            id="search_simple_keyword">
                    </div>
                    <div class="form-group">
                        <button class=" btn btn-default " type="submit" id="search_simple_submit"><em
                                class="fa fa-search"></em> Buscar</button>
                    </div>
                </fieldset>
                <input name="id_session" type="hidden" value="'.$sessionId.'" id="id_session">
            </form>
        </div>
        <div class="col-sm-4 text-center">
        </div>
        <div class="col-sm-4 text-right">
        </div>
    </div>
</div>';

$extraConditions = "";
if (!empty($keyWord)) {
    $extraConditions = " AND (u.username LIKE '%$keyWord%' OR u.email LIKE '%$keyWord%' OR u.firstname LIKE '%$keyWord%' OR u.lastname LIKE '%$keyWord%') ";
}

echo '<h2>'.$toolName.'</h2>';
echo '<div class="row">';
echo '<div class="col-md-12">';
$sql = "SELECT DISTINCT sr.user_id FROM $session_rel_course_rel_user_table sr LEFT JOIN $user_table u ON sr.user_id = u.user_id  WHERE sr.session_id IN (".implode(',', $sessionList).") AND sr.status = 0 $extraConditions";
$res = Database::query($sql);
if (Database::num_rows($res) > 0) {
    echo '<table class="table data_table">';
    echo '<tr>
        <th></th>
        <th>'.get_lang('Name').'</th>
        <th>'.get_lang('Groups').'</th>
        <th>'.get_lang('Email').'</th>
        <th>'.get_lang('RegisteredDate').'</th>
        <th>'.get_lang('LastLogins').'</th>
        <th>'.get_lang('Stats').'</th>
        </tr>';
    while ($row = Database::fetch_assoc($res)) {
        $userInfo = api_get_user_info($row['user_id']);
        $userData = _api_format_user($userInfo);
        echo '<tr>';
        echo '<td><img src="'.$userData['avatar_small'].'" /></td>';
        echo '<td style="vertical-align:middle">';
        echo '<a title="'.htmlspecialchars($userData['complete_name']).'" href="main/mySpace/myStudents.php?student='.$userData['id'].'">';
        echo '<span style="font-weight: bold; font-size: 16px;">'.htmlspecialchars($userData['complete_name']).'</span>';
        echo '</a>';
        echo '</td>';
        echo '<td>';
        if (!empty($sessionId)) {
            $list_sessions = SessionManager::get_sessions_list(['s.id' => ['operator' => '=', 'value' => $sessionId]]);
            if (!empty($list_sessions)) {
                foreach ($list_sessions as $session_item) {
                    echo '<li>'.$session_item['name'].'</li>';
                }
            } else {
                echo get_lang('NoSessionsForThisUser');
            }
        } else {
            $list_sessions = SessionManager::get_sessions_by_user($row['user_id'], true);
            if (!empty($list_sessions)) {
                foreach ($list_sessions as $session_item) {
                    echo '<li>'.$session_item['session_name'].'</li>';
                }
            } else {
                echo get_lang('NoSessionsForThisUser');
            }
        }
        echo '</td>';
        echo '<td style="vertical-align:middle">'.$userData['email'].'</td>';
        echo '<td style="vertical-align:middle">'.api_format_date($userData['registration_date'], DATE_FORMAT_SHORT).'</td>';
        echo '<td style="vertical-align:middle">'.api_format_date($userData['last_login'], DATE_FORMAT_SHORT).'</td>';
        echo '<td style="vertical-align:middle">';
        $actions = Display::url(
            Display::return_icon('2rightarrow.png', get_lang('Details')),
            $codePath.'mySpace/myStudents.php?student='.$row['user_id']
        );
        echo $actions;
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

Display::display_footer();
