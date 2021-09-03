<?php

/* For licensing terms, see /license.txt */

/**
 * Plugin.
 *
 * @author Jose Angel Ruiz
 */
$cidReset = true;
require_once __DIR__.'/config.php';
require_once __DIR__.'/src/PromotionsPlugin.php';

api_protect_admin_script();

/** @var \CleanDeletedFilesPlugin $plugin */
$plugin = PromotionsPlugin::create();
$plugin_info = $plugin->get_info();
$isPlatformAdmin = api_is_platform_admin();
$isEnabled = $plugin->get('tool_enable');

if ($isEnabled == "true" && $isPlatformAdmin) {
    if (!empty($_GET['delete_id'])) {
        Database::update(
            PromotionsPlugin::TABLE_PROMOTIONS,
            ['delete_at' => date("Y-m-d"), 'is_delete' => 1],
            ['id = ? ' => (int) $_GET['delete_id']]
        );

        Display::addFlash(Display::return_message($plugin->get_lang('PromotionDeleted')));
        header('Location: admin.php');
        exit;
    }

    $nameTools = $plugin->get_lang("Promotions");
    Display::display_header($nameTools);
    echo Display::page_header($nameTools);

    $form = new FormValidator('promotions');

    $form->addTextarea('content', $plugin->get_lang('Content'), ['cols-size' => [2, 8, 2], 'rows' => 5]);
    $form->addRule('content', get_lang('ThisFieldIsRequired'), 'required');

    $form->addDateTimePicker(
        'end_at',
        $plugin->get_lang('EndAt'),
        ['id' => 'end_at', 'cols-size' => [2, 8, 2]]
    );
    $form->addRule('end_at', get_lang('ThisFieldIsRequired'), 'required');

    $group = [
        $form->addButtonSave(get_lang('Save'), 'submit', true),
    ];
    $form->addGroup($group);

    if ($form->validate()) {
        $formValues = $form->getSubmitValues();

        $check = Security::check_token('post');
        if ($check) {
            $params = [
                'content' => $formValues['content'],
                'ends_at' => $formValues['end_at'],
                'user_id' => api_get_user_id(),
                'create_at' => date("Y-m-d H:i:s"),
                'is_delete' => 0,
            ];
            Database::insert(PromotionsPlugin::TABLE_PROMOTIONS, $params);
            $promotionsId = Database::insert_id();

            Display::addFlash(Display::return_message($plugin->get_lang('CreatedPromotions')));
            header('Location: admin.php');
            exit;
        } else {
            Display::addFlash(Display::return_message($plugin->get_lang('NoToken')));
            header('Location: admin.php');
            exit;
        }
    }

    if (isset($_POST['submit'])) {
        Security::clear_token();
    }
    $token = Security::get_token();
    $form->addElement('hidden', 'sec_token');
    $form->setConstants(['sec_token' => $token]);

    $form->display();

    $contentHtml = Display::page_subheader2(
        $plugin->get_lang('PromotionsList')
    );

    $promotionsData = Database::select(
        '*',
        PromotionsPlugin::TABLE_PROMOTIONS,
        [
            'where' => [
                'is_delete = ?' => [0],
            ],
        ]
    );

    if (count($promotionsData) > 0) {
        $header = [
            ['ID', true],
            [$plugin->get_lang('Content'), true],
            [$plugin->get_lang('CreateAt'), true],
            [$plugin->get_lang('EndAt'), true],
            [get_lang('Actions'), false],
        ];

        $deleteIcon = Display::return_icon(
            'icons/22/delete.png',
            $plugin->get_lang('DeletePromotions'),
            [],
            ICON_SIZE_SMALL
        );

        $data = [];
        foreach ($promotionsData as $item) {
            $options = Display::url(
                $deleteIcon,
                'admin.php?delete_id='.$item['id'],
                ['class' => 'delete_promotion']
            );

            $row = [
                $item['id'],
                $item['content'],
                date("d/m/Y H:i:s", strtotime($item['create_at'])),
                date("d/m/Y H:i:s", strtotime($item['ends_at'])),
                $options,
            ];
            $data[] = $row;
        }

        $contentHtml .= Display::return_sortable_table(
            $header,
            $data
        );
    } else {
        $contentHtml .= '<div class="alert alert-warning">'.$plugin->get_lang('NoPromotions').'</div>';
    }

    echo $contentHtml;

    Display::display_footer();
}
