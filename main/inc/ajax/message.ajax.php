<?php
/* For licensing terms, see /license.txt */

use Chamilo\UserBundle\Entity\User;

$_dont_save_user_course_access = true;

/**
 * Responses to AJAX calls.
 */
require_once __DIR__.'/../global.inc.php';

$action = $_GET['a'];

switch ($action) {
    case 'get_count_notifications':
        if (api_get_configuration_value('notification_event')) {
            $notificationManager = new NotificationEvent();
            $notifications = $notificationManager->getNotificationsByUser(api_get_user_id());
            echo count($notifications);
        }
        break;
    case 'get_notifications':
        if (api_get_configuration_value('notification_event')) {
            $notificationManager = new NotificationEvent();
            $notifications = $notificationManager->getNotificationsByUser(api_get_user_id());
            echo json_encode($notifications);
        }
        break;
    case 'mark_notification_as_read':
        if (api_get_configuration_value('notification_event')) {
            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
            $notificationManager = new NotificationEvent();
            $notificationManager->markAsRead($id);
            echo 1;
        }
        break;
    case 'get_count_message':
        $userId = api_get_user_id();
        $invitations = [];
        // Setting notifications
        $count_unread_message = 0;
        if (api_get_setting('allow_message_tool') === 'true') {
            // get count unread message and total invitations
            $count_unread_message = MessageManager::getCountNewMessagesFromDB($userId);
        }

        if (api_get_setting('allow_social_tool') === 'true') {
            $number_of_new_messages_of_friend = SocialManager::get_message_number_invitation_by_user_id(
                $userId
            );
            $usergroup = new UserGroup();
            $group_pending_invitations = $usergroup->get_groups_by_user(
                $userId,
                GROUP_USER_PERMISSION_PENDING_INVITATION,
                false
            );
            if (!empty($group_pending_invitations)) {
                $group_pending_invitations = count($group_pending_invitations);
            } else {
                $group_pending_invitations = 0;
            }
            $invitations = [
                'ms_friends' => $number_of_new_messages_of_friend,
                'ms_groups' => $group_pending_invitations,
                'ms_inbox' => $count_unread_message,
            ];
        }
        header('Content-type:application/json');
        echo json_encode($invitations);
        break;
    case 'send_message':
        api_block_anonymous_users(false);

        $subject = isset($_REQUEST['subject']) ? trim($_REQUEST['subject']) : null;
        $messageContent = isset($_REQUEST['content']) ? trim($_REQUEST['content']) : null;

        if (empty($subject) || empty($messageContent)) {
            echo Display::return_message(get_lang('ErrorSendingMessage'), 'error');
            exit;
        }

        $courseId = isset($_REQUEST['course_id']) ? (int) $_REQUEST['course_id'] : 0;
        $sessionId = isset($_REQUEST['session_id']) ? (int) $_REQUEST['session_id'] : 0;

        // Add course info
        if (!empty($courseId)) {
            $courseInfo = api_get_course_info_by_id($courseId);
            if (!empty($courseInfo)) {
                if (empty($sessionId)) {
                    $courseNotification = sprintf(get_lang('ThisEmailWasSentViaCourseX'), $courseInfo['title']);
                } else {
                    $sessionInfo = api_get_session_info($sessionId);
                    if (!empty($sessionInfo)) {
                        $courseNotification = sprintf(
                            get_lang('ThisEmailWasSentViaCourseXInSessionX'),
                            $courseInfo['title'],
                            $sessionInfo['name']
                        );
                    }
                }
                $messageContent .= '<br /><br />'.$courseNotification;
            }
        }

        $result = MessageManager::send_message($_REQUEST['user_id'], $subject, $messageContent);
        if ($result) {
            echo Display::return_message(get_lang('MessageHasBeenSent'), 'confirmation');
        } else {
            echo Display::return_message(get_lang('ErrorSendingMessage'), 'confirmation');
        }
        break;
    case 'send_invitation':
        api_block_anonymous_users(false);

        $subject = isset($_REQUEST['subject']) ? trim($_REQUEST['subject']) : null;
        $invitationContent = isset($_REQUEST['content']) ? trim($_REQUEST['content']) : null;

        SocialManager::sendInvitationToUser($_REQUEST['user_id'], $subject, $invitationContent);
        break;
    case 'find_users':
        if (api_is_anonymous()) {
            echo '';
            break;
        }

        $showEmail = api_get_setting('show_email_addresses') === 'true';
        $return = ['items' => []];

        $studentOnlyViewFriendAndCourses = api_get_configuration_value('enable_student_only_view_friend_and_courses');
        if ($studentOnlyViewFriendAndCourses && $_user['status'] == STUDENT) {
            $accessUrlId = api_get_multiple_access_url() ? api_get_current_access_url_id() : 1;
            $userList = [];

            $sql = "SELECT DISTINCT U.user_id
                    FROM access_url_rel_user R, user_rel_user UF
                    INNER JOIN user AS U ON UF.friend_user_id = U.user_id
                    WHERE
                        U.active = 1 AND
                        U.status != 6 AND
                        UF.relation_type NOT IN(".USER_RELATION_TYPE_DELETED.", ".USER_RELATION_TYPE_RRHH.") AND
                        UF.user_id = ".api_get_user_id()." AND
                        UF.friend_user_id != ".api_get_user_id()." AND
                        U.user_id = R.user_id AND
                        R.access_url_id = $accessUrlId";
            $res = Database::query($sql);
            while ($row = Database::fetch_assoc($res)) {
                $userList[] = $row['user_id'];
            }

            $sql = "SELECT DISTINCT SCU.user_id FROM session_rel_user SU
                    INNER JOIN session_rel_course_rel_user SCU ON SU.session_id=SCU.session_id
                    WHERE SU.user_id=".api_get_user_id()." AND SCU.user_id != ".api_get_user_id()." AND SCU.visibility = 1";
            $res = Database::query($sql);
            while ($row = Database::fetch_assoc($res)) {
                $userList[] = $row['user_id'];
            }

            $userList = array_unique($userList);

            foreach ($userList as $user) {
                $userInfo = api_get_user_info($user);
                if (stripos($userInfo['firstname'], $_REQUEST['q']) === false &&
                    stripos($userInfo['lastname'], $_REQUEST['q']) === false &&
                    stripos($userInfo['username'], $_REQUEST['q']) === false &&
                    stripos($userInfo['email'], $_REQUEST['q']) === false
                ) {
                    continue;
                }

                $userName = $userInfo['complete_name'];

                if ($showEmail) {
                    $userName .= " (".$userInfo['email'].")";
                }

                $return['items'][] = [
                    'text' => $userName,
                    'id' => $user,
                ];
            }
        } else {
            $repo = UserManager::getRepository();
            $users = $repo->findUsersToSendMessage(
                api_get_user_id(),
                $_REQUEST['q'],
                $_REQUEST['page_limit']
            );

            /** @var User $user */
            foreach ($users as $user) {
                $userName = UserManager::formatUserFullName($user, true);

                if ($showEmail) {
                    $userName .= " ({$user->getEmail()})";
                }

                $return['items'][] = [
                    'text' => $userName,
                    'id' => $user->getId(),
                ];
            }
        }
        header('Content-type:application/json');
        echo json_encode($return);
        break;
    default:
        echo '';
}
exit;
