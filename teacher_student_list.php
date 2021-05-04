<?php

use Chamilo\CoreBundle\Entity\Course;
use Chamilo\CoreBundle\Entity\Repository\SequenceResourceRepository;
use Chamilo\CoreBundle\Entity\Repository\SessionRepository;
use Chamilo\CoreBundle\Entity\SequenceResource;
use Chamilo\CoreBundle\Entity\Session;
use Chamilo\CoreBundle\Entity\SessionRelCourseRelUser;
use Chamilo\CoreBundle\Entity\SessionRelCourse;

$cidReset = true;
require_once 'main/inc/global.inc.php';

api_block_anonymous_users();

if (!api_is_teacher()) {
    api_not_allowed(true);
}

$codePath = api_get_path(WEB_CODE_PATH);
$coursePath = api_get_path(WEB_COURSE_PATH);
$pluginPath = api_get_path(WEB_PLUGIN_PATH);
$toolName = get_lang('Students');
$userId = api_get_user_id();
$controller = new IndexManager(get_lang('MyCourses'));
$courseAndSessions = $controller->returnCoursesAndSessionsViewBySession($userId, true);

$session_table = Database::get_main_table(TABLE_MAIN_SESSION);
$session_rel_course_rel_user_table = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$sessionList = [];
$sql = "SELECT DISTINCT session_id FROM $session_rel_course_rel_user_table WHERE user_id='".$userId."' AND status = 2";
$result = Database::query($sql);
while ($row = Database::fetch_assoc($result)) {
    $sessionList[] = $row['session_id'];
}

Display::display_header($toolName);
echo Display::page_header($toolName);
echo '<div class="row">';
echo '<div class="col-md-12">';
$sql = "SELECT DISTINCT user_id FROM $session_rel_course_rel_user_table WHERE session_id IN (".implode(',', $sessionList).") AND status = 0";
$res = Database::query($sql);
if (Database::num_rows($res) > 0) {
    echo '<table class="table data_table">';
    echo '<tr><th></th><th>Nombre</th><th>Correo electrónico</th><th>F. registro</th><th>Último login</th><th>Estadisticas</th></tr>';
    while ($row = Database::fetch_assoc($res)) {
        $userInfo = api_get_user_info($row['user_id']);
        $userData = _api_format_user($userInfo);
        echo '<tr>';
        echo '<td><img src="'.$userData['avatar_small'].'" /></td>';
        echo '<td style="vertical-align:middle">';
        echo '<a title="'.htmlspecialchars($userData['complete_name']).'" href="'.$userData['profile_url'].'">';
        echo '<span style="font-weight: bold; font-size: 16px;">'.htmlspecialchars($userData['complete_name']).'</span>';
        echo '</a>';
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
