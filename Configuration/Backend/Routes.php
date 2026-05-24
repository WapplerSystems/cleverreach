<?php
declare(strict_types=1);

return [
    'ajax_cleverreach_form_editor_clients' => [
        'path' => '/ajax/cleverreach/form-editor/clients',
        'access' => 'public',
        'ajax' => true,
        'target' => \WapplerSystems\Cleverreach\Controller\Backend\FormEditorAjaxController::class . '::getClientsAction',
    ],
    'ajax_cleverreach_form_editor_groups' => [
        'path' => '/ajax/cleverreach/form-editor/groups',
        'access' => 'public',
        'ajax' => true,
        'target' => \WapplerSystems\Cleverreach\Controller\Backend\FormEditorAjaxController::class . '::getGroupsAction',
    ],
    'ajax_cleverreach_form_editor_forms' => [
        'path' => '/ajax/cleverreach/form-editor/forms',
        'access' => 'public',
        'ajax' => true,
        'target' => \WapplerSystems\Cleverreach\Controller\Backend\FormEditorAjaxController::class . '::getFormsAction',
    ],
];