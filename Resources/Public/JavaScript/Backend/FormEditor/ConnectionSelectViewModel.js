/**
 * Module: @wapplersystems/cleverreach/Backend/FormEditor/ConnectionSelectViewModel.js
 *
 * Replaces the Inspector-TextEditor inputs of the CleverReach finishers with
 * <select>s populated from the backend:
 *
 *  - oauthClient: list of active CleverReach OAuth clients
 *  - groupId:     groups (Empfängerlisten) of the chosen client
 *  - formId:      forms (Anmeldeformulare) of the chosen group; reloads when
 *                 oauthClient or groupId changes.
 *
 * Using Inspector-TextEditor (instead of Inspector-SingleSelectEditor) avoids
 * TYPO3's HMAC "limitedAllowedValues" validation, which rejects dynamic values
 * not listed in the static YAML selectOptions.
 */

import AjaxRequest from '@typo3/core/ajax/ajax-request.js';

const FINISHER_IDENTIFIERS = ['CleverreachOptIn', 'CleverreachOptOut'];

let _formEditorApp = null;

/**
 * Per-inspector-render state. Reset whenever the oauthClient editor is
 * (re-)rendered, since that runs before groupId/formId in the YAML order.
 */
let _state = null;

function freshState() {
    return {
        collectionElementId: null,
        clientUid: '',
        groupId: '',
        groupSelect: null,
        groupInput: null,
        formId: '',
        formSelect: null,
        formInput: null,
    };
}

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

function readModelProperty(rawPath, collectionElementIdentifier) {
    try {
        const propertyPath = _formEditorApp.buildPropertyPath(
            rawPath,
            collectionElementIdentifier,
            'finishers'
        );
        return _formEditorApp.getCurrentlySelectedFormElement().get(propertyPath);
    } catch (e) {
        return undefined;
    }
}

function syncInputFromSelect(input, select) {
    input.value = select.value;
    input.dispatchEvent(new Event('keyup', { bubbles: true }));
}

function buildSelect(currentValue, options) {
    const select = document.createElement('select');
    select.className = 'form-select form-control';
    options.forEach(function (opt) {
        const option = document.createElement('option');
        option.value = opt.value;
        option.textContent = opt.label;
        if (opt.value === currentValue) {
            option.selected = true;
        }
        select.appendChild(option);
    });
    return select;
}

function replaceInputWithSelect(input, select) {
    input.style.display = 'none';
    input.parentNode.insertBefore(select, input);
}

async function handleOauthClientEditor(editorHtml, currentValue, collectionElementIdentifier) {
    const root = resolveEditorRoot(editorHtml);
    if (!root) {
        return;
    }
    const input = root.querySelector('[data-template-property="propertyPath"]');
    if (!input) {
        return;
    }

    _state = freshState();
    _state.collectionElementId = collectionElementIdentifier;
    _state.clientUid = String(currentValue ?? '');
    _state.groupId = String(readModelProperty('options.groupId', collectionElementIdentifier) ?? '');
    _state.formId = String(readModelProperty('options.formId', collectionElementIdentifier) ?? '');

    let clients;
    try {
        const response = await new AjaxRequest(TYPO3.settings.ajaxUrls['cleverreach_form_editor_clients']).get();
        clients = await response.resolve();
    } catch (e) {
        console.error('CleverReach: Could not load OAuth clients', e);
        return;
    }

    const select = buildSelect(_state.clientUid, clients);
    select.addEventListener('change', function () {
        syncInputFromSelect(input, select);
        _state.clientUid = select.value;
        refreshGroupSelect();
    });
    replaceInputWithSelect(input, select);
}

async function handleGroupIdEditor(editorHtml, currentValue, collectionElementIdentifier) {
    const root = resolveEditorRoot(editorHtml);
    if (!root) {
        return;
    }
    const input = root.querySelector('[data-template-property="propertyPath"]');
    if (!input) {
        return;
    }

    if (!_state || _state.collectionElementId !== collectionElementIdentifier) {
        _state = freshState();
        _state.collectionElementId = collectionElementIdentifier;
        _state.clientUid = String(readModelProperty('options.oauthClient', collectionElementIdentifier) ?? '');
        _state.formId = String(readModelProperty('options.formId', collectionElementIdentifier) ?? '');
    }
    _state.groupId = String(currentValue ?? '');

    const select = document.createElement('select');
    select.className = 'form-select form-control';
    select.addEventListener('change', function () {
        syncInputFromSelect(input, select);
        _state.groupId = select.value;
        refreshFormSelect();
    });
    replaceInputWithSelect(input, select);

    _state.groupSelect = select;
    _state.groupInput = input;

    await refreshGroupSelect();
}

async function handleFormIdEditor(editorHtml, currentValue, collectionElementIdentifier) {
    const root = resolveEditorRoot(editorHtml);
    if (!root) {
        return;
    }
    const input = root.querySelector('[data-template-property="propertyPath"]');
    if (!input) {
        return;
    }

    if (!_state || _state.collectionElementId !== collectionElementIdentifier) {
        _state = freshState();
        _state.collectionElementId = collectionElementIdentifier;
        _state.clientUid = String(readModelProperty('options.oauthClient', collectionElementIdentifier) ?? '');
        _state.groupId = String(readModelProperty('options.groupId', collectionElementIdentifier) ?? '');
    }
    _state.formId = String(currentValue ?? '');

    const select = document.createElement('select');
    select.className = 'form-select form-control';
    select.addEventListener('change', function () {
        syncInputFromSelect(input, select);
        _state.formId = select.value;
    });
    replaceInputWithSelect(input, select);

    _state.formSelect = select;
    _state.formInput = input;

    await refreshFormSelect();
}

async function refreshGroupSelect() {
    if (!_state || !_state.groupSelect || !_state.groupInput) {
        return;
    }
    const select = _state.groupSelect;
    const input = _state.groupInput;

    select.innerHTML = '';
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = _state.clientUid ? '…' : '— OAuth Client wählen —';
    select.appendChild(placeholder);

    if (!_state.clientUid) {
        syncInputFromSelect(input, select);
        await refreshFormSelect();
        return;
    }

    let groups;
    try {
        const url = TYPO3.settings.ajaxUrls['cleverreach_form_editor_groups']
            + '?clientUid=' + encodeURIComponent(_state.clientUid);
        const response = await new AjaxRequest(url).get();
        groups = await response.resolve();
    } catch (e) {
        console.error('CleverReach: Could not load groups', e);
        placeholder.textContent = '(Fehler beim Laden)';
        return;
    }

    select.innerHTML = '';
    let matched = false;
    groups.forEach(function (item) {
        const option = document.createElement('option');
        option.value = item.value;
        option.textContent = item.label;
        if (item.value === _state.groupId) {
            option.selected = true;
            matched = true;
        }
        select.appendChild(option);
    });

    if (!matched && _state.groupId !== '') {
        const lost = document.createElement('option');
        lost.value = _state.groupId;
        lost.textContent = _state.groupId + ' (nicht in Liste)';
        lost.selected = true;
        select.insertBefore(lost, select.firstChild);
    }

    syncInputFromSelect(input, select);
    await refreshFormSelect();
}

async function refreshFormSelect() {
    if (!_state || !_state.formSelect || !_state.formInput) {
        return;
    }
    const select = _state.formSelect;
    const input = _state.formInput;

    select.innerHTML = '';
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = !_state.clientUid
        ? '— OAuth Client wählen —'
        : (!_state.groupId ? '— Group wählen —' : '…');
    select.appendChild(placeholder);

    if (!_state.clientUid || !_state.groupId) {
        syncInputFromSelect(input, select);
        return;
    }

    let forms;
    try {
        const url = TYPO3.settings.ajaxUrls['cleverreach_form_editor_forms']
            + '?clientUid=' + encodeURIComponent(_state.clientUid)
            + '&groupId=' + encodeURIComponent(_state.groupId);
        const response = await new AjaxRequest(url).get();
        forms = await response.resolve();
    } catch (e) {
        console.error('CleverReach: Could not load forms', e);
        placeholder.textContent = '(Fehler beim Laden)';
        return;
    }

    select.innerHTML = '';
    let matched = false;
    forms.forEach(function (item) {
        const option = document.createElement('option');
        option.value = item.value;
        option.textContent = item.label;
        if (item.value === _state.formId) {
            option.selected = true;
            matched = true;
        }
        select.appendChild(option);
    });

    if (!matched && _state.formId !== '') {
        const lost = document.createElement('option');
        lost.value = _state.formId;
        lost.textContent = _state.formId + ' (nicht in Liste)';
        lost.selected = true;
        select.insertBefore(lost, select.firstChild);
    }

    syncInputFromSelect(input, select);
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
                collectionName !== 'finishers' ||
                !FINISHER_IDENTIFIERS.includes(collectionElementIdentifier)
            ) {
                return;
            }

            const editorIdentifier = editorConfiguration['identifier'];
            if (!['oauthClient', 'groupId', 'formId'].includes(editorIdentifier)) {
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

            switch (editorIdentifier) {
                case 'oauthClient':
                    handleOauthClientEditor(editorHtml, currentValue, collectionElementIdentifier);
                    break;
                case 'groupId':
                    handleGroupIdEditor(editorHtml, currentValue, collectionElementIdentifier);
                    break;
                case 'formId':
                    handleFormIdEditor(editorHtml, currentValue, collectionElementIdentifier);
                    break;
            }
        }
    );
}

export function bootstrap(formEditorApp) {
    _formEditorApp = formEditorApp;
    _subscribeEvents();
}
