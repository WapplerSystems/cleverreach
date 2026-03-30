<?php
declare(strict_types=1);

return [
    'ajax_cleverreach_form_editor_connections' => [
        'path' => '/ajax/cleverreach/form-editor/connections',
        'access' => 'public',
        'ajax' => true,
        'target' => \WapplerSystems\Cleverreach\Controller\Backend\FormEditorAjaxController::class . '::getConnectionsAction',
    ],
];