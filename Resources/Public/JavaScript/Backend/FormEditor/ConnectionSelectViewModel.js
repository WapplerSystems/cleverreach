/**
 * Module: @wapplersystems/cleverreach/Backend/FormEditor/ConnectionSelectViewModel.js
 *
 * Replaces the Inspector-TextEditor input for the "oauthClient" finisher
 * property with a <select> populated from active CleverReach OAuth clients
 * via AJAX.
 *
 * Using Inspector-TextEditor (instead of Inspector-SingleSelectEditor) avoids
 * TYPO3's HMAC "limitedAllowedValues" validation, which rejects dynamic values
 * not listed in the static YAML selectOptions.
 */

import $ from 'jquery';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';

const EDITOR_IDENTIFIER = 'oauthClient';

let _formEditorApp = null;

function getPublisherSubscriber() {
    return _formEditorApp.getPublisherSubscriber();
}

/**
 * Fetches active CleverReach OAuth clients via AJAX and replaces the
 * Inspector-TextEditor input with a <select>.
 *
 * The hidden input is kept so the existing "keyup" listener of
 * Inspector-TextEditor syncs the value into the form model.
 *
 * @param {jQuery} editorHtml
 * @param {string} currentValue  Currently stored client UID
 */
async function populateClientSelect(editorHtml, currentValue) {
    const $input = editorHtml.find('[data-template-property="propertyPath"]');
    if (!$input.length) {
        return;
    }

    let clients;
    try {
        const response = await new AjaxRequest(TYPO3.settings.ajaxUrls['cleverreach_form_editor_clients']).get();
        clients = await response.resolve();
    } catch (e) {
        console.error('CleverReach: Could not load OAuth clients', e);
        return;
    }

    const $select = $('<select class="form-select form-control"></select>');

    clients.forEach(function (client) {
        $select.append(
            $('<option></option>')
                .val(client.value)
                .text(client.label)
                .prop('selected', client.value === currentValue)
        );
    });

    $select.on('change', function () {
        $input.val($(this).val()).trigger('keyup');
    });

    $input.hide().before($select);
}

function _subscribeEvents() {
    getPublisherSubscriber().subscribe(
        'view/inspector/editor/insert/perform',
        function (topic, args) {
            const editorConfiguration         = args[0];
            const editorHtml                  = args[1];
            const collectionElementIdentifier = args[2];
            const collectionName              = args[3];

            if (
                editorConfiguration['identifier'] !== EDITOR_IDENTIFIER ||
                collectionName !== 'finishers'
            ) {
                return;
            }

            const propertyPath = _formEditorApp.buildPropertyPath(
                editorConfiguration['propertyPath'],
                collectionElementIdentifier,
                collectionName
            );
            const currentValue = String(
                _formEditorApp.getCurrentlySelectedFormElement().get(propertyPath) ?? ''
            );

            populateClientSelect(editorHtml, currentValue);
        }
    );
}

export function bootstrap(formEditorApp) {
    _formEditorApp = formEditorApp;
    _subscribeEvents();
}