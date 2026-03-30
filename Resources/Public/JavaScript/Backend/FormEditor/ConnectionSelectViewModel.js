/**
 * Module: @wapplersystems/cleverreach/Backend/FormEditor/ConnectionSelectViewModel.js
 *
 * Replaces the Inspector-TextEditor input for the oauthClient finisher property
 * with a <select> populated from active CleverReach OAuth clients via AJAX.
 *
 * Using Inspector-TextEditor (instead of Inspector-SingleSelectEditor) avoids
 * TYPO3's HMAC "limitedAllowedValues" validation, which would reject dynamically
 * loaded values that are not listed in the static YAML selectOptions.
 */

import $ from 'jquery';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';

const EDITOR_IDENTIFIER = 'oauthClient';

let _formEditorApp = null;

function getPublisherSubscriber() {
    return _formEditorApp.getPublisherSubscriber();
}

/**
 * Fetches active CleverReach OAuth clients and replaces the text input
 * rendered by Inspector-TextEditor with a <select> widget.
 *
 * Inspector-TextEditor binds changes via a "keyup paste" listener on the
 * input element ([data-template-property="propertyPath"]). We hide that
 * input, inject a <select> before it, and mirror the selected value back
 * into the hidden input, triggering "keyup" so the form model is updated.
 *
 * @param {jQuery} editorHtml   jQuery object of the rendered editor DOM node
 * @param {string} currentValue The currently stored property value (client UID)
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
        const isSelected = client.value === currentValue;
        const $option = $('<option></option>')
            .val(client.value)
            .text(client.label)
            .prop('selected', isSelected);
        $select.append($option);
    });

    $select.on('change', function () {
        const newValue = $(this).val();
        $input.val(newValue).trigger('keyup');
    });

    $input.hide().before($select);
}

function _subscribeEvents() {
    getPublisherSubscriber().subscribe(
        'view/inspector/editor/insert/perform',
        function (topic, args) {
            const editorConfiguration = args[0];
            const editorHtml = args[1];
            const collectionElementIdentifier = args[2];
            const collectionName = args[3];

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
            const currentValue = String(_formEditorApp.getCurrentlySelectedFormElement().get(propertyPath) ?? '');

            populateClientSelect(editorHtml, currentValue);
        }
    );
}

export function bootstrap(formEditorApp) {
    _formEditorApp = formEditorApp;
    _subscribeEvents();
}