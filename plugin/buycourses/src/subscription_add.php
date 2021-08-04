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

$globalSettingsParams = $plugin->getGlobalParameters();

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

$form->addElement(
    'number',
    'tax_perc',
    [$plugin->get_lang('TaxPerc'), $plugin->get_lang('TaxPercDescription'), '%'],
    ['step' => 1, 'placeholder' => $globalSettingsParams['global_tax_perc'].'% '.$plugin->get_lang('ByDefault')]
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

$frequencies = $plugin->getFrequencies();

$platformCommission = $plugin->getPlatformCommission();
$form->addHtml(
    '
    <div class="form-group">
        <div class="col-sm-10">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Frequencies</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-5">
                            <div class="form-group ">
                                <label for="frequency_val" class="col-sm-3 control-label">
                                    Frequency
                                </label>
                                <div class="col-sm-8">
                                    <div class="dropdown bootstrap-select form-control bs3 dropup">
                                        <select class="selectpicker form-control"
                                            data-live-search="true" name="frequency_value" id="frequency_value" tabindex="null">
                                            <option value="7">Weekly</option>
                                            <option value="30">Monthly</option>
                                            <option value="60">Quarterly</option>
                                            <option value="180">Biannual</option>
                                            <option value="360">annual</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-1"></div>
                            </div>
                            <div class="form-group ">
                                <label for="frequency_price" class="col-sm-3 control-label">
                                    Price
                                </label>
                                <div class="col-sm-8">
                                    <input class=" form-control" name="frequency_price" type="text" id="frequency_price">
                                </div>
                                <div class="col-sm-1"></div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <a class=" btn btn-primary " name="add" type="submit"><em class="fa fa-plus"></em> Add</a>
                                </div>
                                <div class="col-sm-2"></div>
                            </div>            
                        </div>
                        <div class="col-sm-7">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th>Days</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <!--{% for frequency in frequencies %}
                                        <tr>
                                            <td>{{ frequency.days }}</td>
                                            <td>{{ account.price }}</td>
                                            <td>
                                                <a class="btn btn-danger btn-sm">
                                                    <em class="fa fa-remove"></em>
                                                </a>
                                            </td>
                                        </tr>
                                    {% endfor %}-->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-2">
        </div>
    </div>
    '
);

$button = $form->addButtonSave(get_lang('Save'));

if (empty($currency)) {
    $button->setAttribute('disabled');
}

if ($form->validate()) {
    $formValues = $form->getSubmitValues();

    $subscription['name'] = $formValues['subscription_name'];
    $subscription['description'] = $formValues['description_name'];
    $subscription['tax_perc'] = $formValues['tax_perc'];
    $subscription['active'] = $formValues['active'];

    $subscription['courses'] = isset($formValues['courses']) ? $formValues['courses'] : [];
    $subscription['sessions'] = isset($formValues['sessions']) ? $formValues['sessions'] : [];
    $subscription['frequencies'] = isset($formValues['frequencies']) ? $formValues['frequencies'] : [];

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
$template->assign('items_form', $form->returnForm());

$content = $template->fetch('buycourses/view/subscription_add.tpl');
$template->assign('content', $content);

$template->display_one_col_template();
