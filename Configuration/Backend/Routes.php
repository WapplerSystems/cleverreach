<?php
declare(strict_types=1);

return [
    'ajax_cleverreach_form_editor_clients' => [
        'path' => '/ajax/cleverreach/form-editor/clients',
        'access' => 'public',
        'ajax' => true,
        'target' => \WapplerSystems\Cleverreach\Controller\Backend\FormEditorAjaxController::class . '::getClientsAction',
    ],
];
