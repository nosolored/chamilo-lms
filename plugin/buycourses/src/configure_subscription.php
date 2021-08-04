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

$productId = $_REQUEST['id'];
$productType = $_REQUEST['type'];

if (!isset($productId) || !isset($productType)) {
    api_not_allowed();
}

$queryString = 'id='.intval($_REQUEST['id']).'&type='.intval($_REQUEST['type']);

$editingCourse = $productType === BuyCoursesPlugin::PRODUCT_TYPE_COURSE;
$editingSession = $productType === BuyCoursesPlugin::PRODUCT_TYPE_SESSION;

$plugin = BuyCoursesPlugin::create();

$entityManager = Database::getManager();
$userRepo = UserManager::getRepository();
$currency = $plugin->getSelectedCurrency();

if (empty($currency)) {
    Display::addFlash(
        Display::return_message($plugin->get_lang('CurrencyIsNotConfigured'), 'error')
    );
}

$subscriptions = $plugin->getSubscriptions($productType, $productId );

$taxtPerc = $subscriptions[0]['tax_perc'];

$currencyIso = null;

if ($editingCourse) {
    $course = $entityManager->find('ChamiloCoreBundle:Course', $id);
    if (!$course) {
        api_not_allowed(true);
    }

    $courseItem = $plugin->getCourseForConfiguration($course, $currency);

    $currencyIso = $courseItem['currency'];
    $formDefaults = [
        'product_type' => get_lang('Course'),
        'id' => $courseItem['course_id'],
        'type' => BuyCoursesPlugin::PRODUCT_TYPE_COURSE,
        'name' => $courseItem['course_title'],
        'visible' => $courseItem['visible'],
    ];
} else if ($editingSession) {
    if (!$includeSession) {
        api_not_allowed(true);
    }

    $session = $entityManager->find('ChamiloCoreBundle:Session', $id);
    if (!$session) {
        api_not_allowed(true);
    }

    $sessionItem = $plugin->getSessionForConfiguration($session, $currency);

    $currencyIso = $sessionItem['currency'];
    $formDefaults = [
        'product_type' => get_lang('Session'),
        'id' => $session->getId(),
        'type' => BuyCoursesPlugin::PRODUCT_TYPE_SESSION,
        'name' => $sessionItem['session_name'],
        'visible' => $sessionItem['visible'],
    ];
} else {
    api_not_allowed(true);
}

$globalSettingsParams = $plugin->getGlobalParameters();

$form = new FormValidator('add_subscription');

$form->addText('product_type', $plugin->get_lang('ProductType'), false);
$form->addText('name', get_lang('Name'), false);

$form->addElement(
    'number',
    'tax_perc',
    [$plugin->get_lang('TaxPerc'), $plugin->get_lang('TaxPercDescription'), '%'],
    ['step' => 1, 'placeholder' => $globalSettingsParams['global_tax_perc'].'% '.$plugin->get_lang('ByDefault')]
);

$frequenciesOptions = $plugin->getFrequencies();

$frequencyForm = new FormValidator('frequency_config');

if ($frequencyForm->validate()) {
    $frequencyFormValues = $frequencyForm->getSubmitValues();

    $subscription['product_id'] = $frequencyFormValues['id'];
    $subscription['product_type'] = $frequencyFormValues['type'];
    $subscription['tax_perc'] = $frequencyFormValues['tax_perc'] != '' ? (int) $frequencyFormValues['tax_perc'] : null;
    $duration = $frequencyFormValues['duration'];
    $price = $frequencyFormValues['price'];

    $subscription['frequencies'] = [[$duration, $price]];

    $result = $plugin->addNewSubscription($subscription);

    Display::addFlash(
        Display::return_message(get_lang('Saved'), 'success')
    );

    header('Location:'.api_get_self().'?'.$queryString);
    exit;
}

$frequencyForm->addSelect(
    'duration',
    $plugin->get_lang('Duration'),
    $frequenciesOptions,
    ['cols-size' => [3, 8, 1]]
);

$frequencyForm->addElement(
    'number',
    'price',
    $plugin->get_lang('Price'),
    false,
    [
        'step' => 1,
        'cols-size' => [3, 8, 1]
    ]
);

$frequencyForm->addButtonCreate('');

$frequencyFormDefaults = [
    'id' => $productId,
    'type' => $productType,
    'tax_perc' => $taxtPerc,
];

$frequencyForm->setDefaults($frequencyFormDefaults);

$frequencies = $subscriptions;

$form->addHidden('type', null);
$form->addHidden('id', null);
$button = $form->addButtonSave(get_lang('Save'));

if (empty($currency)) {
    $button->setAttribute('disabled');
}

if ($form->validate()) {
    $formValues = $form->getSubmitValues();
    $productId = $formValues['id'];
    $productType = $formValues['type'];
    $taxPerc = $formValues['tax_perc'] != '' ? (int) $formValues['tax_perc'] : null;

    $result = $plugin->updateSubscriptions($productType, $productId, $taxPerc);

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
$template->assign('items_form', $form->returnForm());
$template->assign('frequency_form', $frequencyForm->returnForm());
$template->assign('frequencies', $frequencies);

$content = $template->fetch('buycourses/view/configure_subscription.tpl');
$template->assign('content', $content);

$template->display_one_col_template();
