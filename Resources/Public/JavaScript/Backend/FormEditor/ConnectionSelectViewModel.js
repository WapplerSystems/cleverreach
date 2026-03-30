/**
 * Module: @wapplersystems/cleverreach/Backend/FormEditor/ConnectionSelectViewModel.js
 *
 * Populates the oauthConnection select field in the CleverReach finisher
 * with active oauth-service connections fetched from the backend.
 */

import $ from 'jquery';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';

const EDITOR_IDENTIFIER = 'oauthConnection';

let _formEditorApp = null;

function getPublisherSubscriber() {
    return _formEditorApp.getPublisherSubscriber();
}

/**
 * Fetches active CleverReach connections and replaces the <select> options.
 *
 * IMPORTANT: The Inspector-SingleSelectEditor stores the real value via
 * jQuery .data({value: ...}) on each <option> – the option's value attribute
 * is just a numeric index. The change handler already attached by
 * inspector-component.js reads .data('value'), so we must follow the same pattern.
 *
 * @param {jQuery} editorHtml   jQuery object of the rendered editor DOM node
 * @param {string} currentValue The currently stored property value (connection UID)
 */
async function populateConnectionSelect(editorHtml, currentValue) {
    const $select = editorHtml.find('[data-template-property="selectOptions"]');
    if (!$select.length) {
        return;
    }

    let connections;
    try {
        const response = await new AjaxRequest(TYPO3.settings.ajaxUrls['cleverreach_form_editor_connections']).get();
        connections = await response.resolve();
    } catch (e) {
        console.error('CleverReach: Could not load OAuth connections', e);
        return;
    }

    $select.empty();

    connections.forEach(function (conn, index) {
        const isSelected = conn.value === currentValue;
        const option = new Option(conn.label, index.toString(), false, isSelected);
        $(option).data({ value: conn.value });
        $select.append(option);
    });
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

            populateConnectionSelect(editorHtml, currentValue);
        }
    );
}

export function bootstrap(formEditorApp) {
    _formEditorApp = formEditorApp;
    _subscribeEvents();
}