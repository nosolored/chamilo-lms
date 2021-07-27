<?php
/* For license terms, see /license.txt */

/**
 * Configuration page for subscriptions for the Buy Courses plugin.
 *
 * @package chamilo.plugin.buycourses
 */
$cidReset = true;

require_once __DIR__.'/../../../main/inc/global.inc.php';

api_protect_admin_script(true);

$plugin = BuyCoursesPlugin::create();

$includeSession = $plugin->get('include_sessions') === 'true';

$entityManager = Database::getManager();
$userRepo = UserManager::getRepository();
$currency = $plugin->getSelectedCurrency();

if (empty($currency)) {
    Display::addFlash(
        Display::return_message($plugin->get_lang('CurrencyIsNotConfigured'), 'error')
    );
}

$currencyIso = null;

$coursesList = CourseManager::get_courses_list(
    0,
    0,
    'title',
    'asc',
    -1,
    null,
    api_get_current_access_url_id(),
    false,
    [],
    []
);

foreach ($coursesList as $course) {
    $courses[$course['id']] = $course['title'];
}

$sessionsList = SessionManager::get_sessions_list(
    [],
    [],
    null,
    null,
    api_get_current_access_url_id(),
    []
);

foreach ($sessionsList as $session) {
    $sessions[$session['id']] = $session['name'];
}

$form = new FormValidator('add_subscription');

$form->addElement(
    'text',
    'subscription_name',
    $plugin->get_lang('SubscriptionName')
);

$form->addElement(
    'text',
    'subscription_description',
    $plugin->get_lang('SubscriptionDescription')
);

$form->addCheckBox('active', get_lang('Active'));
$form->addElement(
    'advmultiselect',
    'courses',
    get_lang('Courses'),
    $courses
);

if ($includeSession) {
    $form->addElement(
        'advmultiselect',
        'sessions',
        get_lang('Sessions'),
        $sessions
    );
}

$button = $form->addButtonSave(get_lang('Save'));

if (empty($currency)) {
    $button->setAttribute('disabled');
}

if ($form->validate()) {
    $formValues = $form->getSubmitValues();

    $subscription['name'] = $formValues['subscription_name'];
    $subscription['description'] = $formValues['description_name'];
    $subscription['active'] = $formValues['active'];

    $subscription['courses'] = isset($formValues['courses']) ? $formValues['courses'] : [];
    $subscription['sessions'] = isset($formValues['sessions']) ? $formValues['sessions'] : [];

    $result = $plugin->addNewSubscription($subscription);

    if ($result) {
        header('Location: '.api_get_path(WEB_PLUGIN_PATH).'buycourses/src/subscriptions.php');
    } else {
        header('Location:'.api_get_self().'?'.$queryString);
    }

    exit;
}

$form->setDefaults($formDefaults);

$templateName = $plugin->get_lang('SubscriptionAdd');
$interbreadcrumb[] = [
    'url' => 'index.php',
    'name' => get_lang('Configuration'),
];
$interbreadcrumb[] = [
    'url' => 'subscriptions.php',
    'name' => $plugin->get_lang('SubscriptionList'),
];

$template = new Template($templateName);
$template->assign('header', $templateName);
$template->assign('content', $form->returnForm());
$template->display_one_col_template();
