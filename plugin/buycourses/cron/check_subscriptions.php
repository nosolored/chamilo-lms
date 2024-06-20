<?php
/* For license terms, see /license.txt */

/**
 * Configuration page for subscriptions for the Buy Courses plugin.
 *
 * @package chamilo.plugin.buycourses
 */
$cidReset = true;

require_once __DIR__.'/../../../main/inc/global.inc.php';

$plugin = BuyCoursesPlugin::create();

$subscriptionsDue = $plugin->getSubscriptionsDue(api_get_utc_datetime());

foreach ($subscriptionDue as $subscriptionsDue) {
    $subscriptionActive = $plugin->checkItemSubscriptionActive($subscriptionDue['user_id'], $subscriptionDue['product_id'], $subscriptionDue['product_type']);

    if (!$subscriptionActive) {
        if ($subscriptionDue['product_type'] === BuyCoursesPlugin::PRODUCT_TYPE_COURSE) {
            CourseManager::unsubscribe_user($subscriptionDue['product_id'], $subscriptionDue['user_id']);
        } elseif ($isSession = $subscriptionDue['product_type'] === BuyCoursesPlugin::PRODUCT_TYPE_SESSION) {
            SessionManager::unsubscribe_user_from_session($subscriptionDue['product_id'], $subscriptionDue['user_id']);
        }

        $plugin->updateSubscriptionSaleExpirationStatus($subscriptionDue['user_id']);
    }
}
