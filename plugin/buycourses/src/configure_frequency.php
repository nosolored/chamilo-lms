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

$subscriptionId = $_REQUEST['subscription_id'];

$queryString = 'subscription_id='.intval($_REQUEST['subscription_id']);

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

$form = new FormValidator('add_frequencies');

$frequencies = $plugin->getFrequencies();

$frequencySelect = $form->addSelect(
    'frequency_value',
    $plugin->get_lang('FrequenciesOptions'),
    [get_lang('Select')]
);

foreach ($frequencies as $frequency) {
    $currencySelect->addOption($frequency['Text'], $frequency['Value']);
}

$form->addElement(
    'number',
    'frecuency_price',
    $plugin->get_lang('FrecuencyPrice')
);

$button = $form->addButtonSave(get_lang('Save'));

if (empty($currency)) {
    $button->setAttribute('disabled');
}

if ($form->validate()) {
    $formValues = $form->getSubmitValues();

    $frequency['subscription_id'] = $formValues['subscription_id'];
    $subscription['days'] = $formValues['frequency_value'];
    $subscription['price'] = $formValues['frequency_price'];

    $result = $plugin->addNewSubscriptionFrequency($subscription);

    if ($result) {
        header('Location: '.api_get_path(WEB_PLUGIN_PATH).'buycourses/src/subscriptions.php');
    } else {
        header('Location:'.api_get_self().'?'.$queryString);
    }

    exit;
}

$formDefaults = [
    'subscription_id' => $subscriptionId,
];

$form->setDefaults($formDefaults);

$templateName = $plugin->get_lang('FrecuencyAdd');
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
