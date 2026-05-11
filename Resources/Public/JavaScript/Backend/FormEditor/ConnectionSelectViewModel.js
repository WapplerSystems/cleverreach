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

import AjaxRequest from '@typo3/core/ajax/ajax-request.js';

const EDITOR_IDENTIFIER = 'oauthClient';
const FINISHER_IDENTIFIERS = ['Cleverreach'];

let _formEditorApp = null;

function getPublisherSubscriber() {
    return _formEditorApp.getPublisherSubscriber();
}

/**
 * Resolves the inspector editor's root DOM element. TYPO3 v14 passes a vanilla
 * Node here; older releases passed a jQuery wrapper, so we accept both.
 */
function resolveEditorRoot(editorHtml) {
    if (!editorHtml) {
        return null;
    }
    if (editorHtml instanceof Element) {
        return editorHtml;
    }
    if (typeof editorHtml.get === 'function') {
        return editorHtml.get(0) ?? null;
    }
    if (typeof editorHtml[0] !== 'undefined') {
        return editorHtml[0];
    }
    return null;
}

/**
 * Fetches active CleverReach OAuth clients via AJAX and replaces the
 * Inspector-TextEditor input with a <select>.
 *
 * The hidden input is kept so the existing "keyup" listener of
 * Inspector-TextEditor syncs the value into the form model.
 */
async function populateClientSelect(editorHtml, currentValue) {
    const root = resolveEditorRoot(editorHtml);
    if (!root) {
        return;
    }
    const input = root.querySelector('[data-template-property="propertyPath"]');
    if (!input) {
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

    const select = document.createElement('select');
    select.className = 'form-select form-control';

    clients.forEach(function (client) {
        const option = document.createElement('option');
        option.value = client.value;
        option.textContent = client.label;
        if (client.value === currentValue) {
            option.selected = true;
        }
        select.appendChild(option);
    });

    select.addEventListener('change', function () {
        input.value = select.value;
        input.dispatchEvent(new Event('keyup', { bubbles: true }));
    });

    input.style.display = 'none';
    input.parentNode.insertBefore(select, input);
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
                collectionName !== 'finishers' ||
                !FINISHER_IDENTIFIERS.includes(collectionElementIdentifier)
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