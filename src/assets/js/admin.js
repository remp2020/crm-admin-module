import "./vendor"

$(document).ready(function() {
    $('.autosize').autosize();

    initSelect2();

    // handling ajax calls and elements created dynamically
    $.nette.ext({
        before: function (xhr, settings) {
            if (!settings.nette) {
                return;
            }
            if (!settings.url) {
                return;
            }

            let target = $(settings.nette.e.target);
            if (!target.hasClass('btn-danger') && !target.closest('.btn').hasClass('btn-danger')) {
                return;
            }

            if (!confirm('Are you sure (nette.ext)?')) {
                settings.nette.e.preventDefault();
                return false;
            }
        }
    });

    // handling standard calls on elements rendered on page load
    $(document).on('click', 'a.btn-danger', function(e) {
        if (!confirm('Are you sure?')) {
            e.preventDefault();
            return false;
        }
    });
    $(document).on('click', '[data-confirm]', function(e) {
        if (!confirm($(this).data('confirm'))) {
            e.preventDefault();
            return false;
        }
    });

    $('.add_note').click(function(ev) {
        ev.preventDefault();

        let item_id = $(this).data('item-id');
        $('#frm-noteForm-item_id').val(item_id);

        let actual_value = $('#item-' + item_id + '-value');
        $('#frm-noteForm-note').val(actual_value.text());
        $('#frm-noteForm').insertBefore($(this));
        $('#frm-noteForm').show();
    });

    $('.changestatusok').click(function (ev) {
        ev.preventDefault();

        $('#sendnotificationbutton').attr('href', $(this).data('send-notification-link'))
        $('#dontsendnotificationbutton').attr('href', $(this).data('dont-send-notification-link'))

        $('#myModal').modal();

        return false;
    });

    $('.checkAll').on('change', function () {
        if ($(this).prop('checked')) {
            $(this).closest('form').find('input:checkbox').not('[disabled]').prop('checked', true);
        } else {
            $(this).closest('form').find('input:checkbox').not('[disabled]').prop('checked', false);
        }
    });

    $('[data-toggle="tooltip"]').tooltip( { html: true } );

    $('input.flatpickr, .flatpickr-wrap').each(function (i) {
        var properties = {
            "allowInput": true,
            "time_24hr": true,
            "dateFormat": "Y-m-d",
            "altInput": true,
            "altFormat": "J M Y",
            onClose(dates, currentDateString, picker) {
                picker.setDate(picker.altInput.value, true, picker.config.altFormat)
            }
        };

        var enableTime = $(this).attr('flatpickr_datetime');
        if (enableTime) {
            properties["enableTime"] = true;
            properties["dateFormat"] = 'Z';
            properties["altFormat"] = 'J M Y H:i';
            properties["defaultHour"] = 0;
        }

        var enableTimeSeconds = $(this).attr('flatpickr_datetime_seconds');
        if (enableTimeSeconds) {
            properties["enableTime"] = true;
            properties["enableSeconds"] = true;
            properties["dateFormat"] = 'Z';
            properties["altFormat"] = 'J M Y H:i:S';
            properties["defaultHour"] = 0;
        }

        var minDate = $(this).attr('flatpickr_mindate');
        if (minDate) {
            properties["minDate"] = minDate;
        }
        var maxDate = $(this).attr('flatpickr_maxdate');
        if (maxDate) {
            properties["maxDate"] = maxDate;
        }

        var wrap = $(this).data('flatpickr_wrap');
        if (wrap) {
            properties['wrap'] = true;
        }

        var allowInvalidPreload = $(this).attr('flatpickr_allow_invalid_preload');
        if (allowInvalidPreload) {
            properties['allowInvalidPreload'] = true;
        }

        this.flatpickr(properties);
    });

    initHtmlEditor();
    initAceEditor(false);
    initCodemirror();
});

window.initSelect2 = function () {
    $('select.select2').each(function () {
        let config = {
            templateResult: function(data) {
                return data.text;
            },
            templateSelection: function(data) {
                return $("<span>" + data.text + "</span>").find('*').remove().end().text().trim();
            },
            escapeMarkup: function(markup) {
                return markup;
            },
            allowClear: true,
            language: $('html').attr('lang'),
            dropdownAutoWidth: true
        };

        let allowClear = $(this).attr('allowClear');
        if (allowClear === 'false') {
            config['allowClear'] = false
        }

        let placeholder = $(this).find('option[value=""]').text();
        if (placeholder.length > 0) {
            config["placeholder"] = placeholder;
        }
        if ($(this).is(':disabled')) {
            config["disabled"] = true;
        }

        let modal = $(this).closest('.modal');
        if (modal.length) {
            // select2 search wouldn't work in modal without this
            config["dropdownParent"] = modal;
        }

        let ajaxUrl = $(this).data('ajax-url');
        if (ajaxUrl) {
            config["ajax"] = {
                "url": ajaxUrl,
                "dataType": "json",
                "delay": 500,
                "processResults": function (data) {
                    let result = [];
                    data.forEach(item => {
                        result.push({
                            "id": item.key,
                            "text": $("<span>" + item.value + "</span>"),
                        });
                    });
                    return {
                        "results": result
                    };
                }
            }
            config["minimumInputLength"] = 2;
        }

        $(this).select2(config);
    });
}

// for selects to have correct width in collapse blocks
$('.collapse').on('show.bs.collapse', function () {
    setTimeout(initSelect2, 0);
});
$('.modal').on('shown.bs.modal', function () {
    setTimeout(initSelect2, 0);
});

function initAceEditor(createDiv) {
    $('.ace').each(function () {
        let el_lang = $(this).attr('data-lang');
        let aceEditorId = $(this).attr('id') + '_div';
        if (createDiv && !$('#' + aceEditorId).length) {
            $(this).parent().prepend('<div id="' + aceEditorId + '"></div>');
        }
        if ($('#' + aceEditorId).length) {
            let editor = ace.edit(($(this)).attr('id') + '_div');
            let textarea = $('#' + ($(this)).attr('id'));
            editor.getSession().setValue(textarea.val());
            editor.getSession().on('change', function () {
                textarea.val(editor.getSession().getValue());
            });
            editor.setTheme("ace/theme/monokai");
            if (el_lang !== 'text') {
                editor.session.setMode("ace/mode/" + el_lang);
            }
        }
    })
}

function initHtmlEditor() {
    const selector = '[data-html-editor]';
    const defaultOptions = {
        semanticKeepAttributes: true,
        semantic: false,
        autogrow: true,
    }
    $(selector).each(function(index, element) {
        let $this = $(element);
        let options = $($this).data('html-editor');
        options = $.extend(true, {}, defaultOptions, options);
        $this.trumbowyg(options);
    });
}

function initCodemirror() {
    const selector = '[data-codeeditor]';
    $(selector).each(function () {
        let element = $(this);
        let mode = element.data('codeeditor');
        const settings = {
            'mode': mode,
            'indentUnit': 4,
            'indentWithTabs': false,
            'inputStyle': 'contenteditable',
            'lineNumbers': true,
            'lineWrapping': true,
            'styleActiveLine': true,
            'continueComments': true,
            'extraKeys': {
                'Ctrl-Space': 'autocomplete',
                'Ctrl-\/': 'toggleComment',
                'Cmd-\/': 'toggleComment',
                'Alt-F': 'findPersistent',
                'Ctrl-F': 'findPersistent',
                'Cmd-F': 'findPersistent'
            },
            'direction': 'ltr',
            'autoCloseBrackets': true,
            'autoCloseTags': true,
            'matchTags': {
                'bothTags': true
            },
        }

        CodeMirror.fromTextArea(element[0], settings);
    });
}
