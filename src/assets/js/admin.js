import "./vendor"
import moment from "moment";
import minMaxTimePlugin from "flatpickr/dist/plugins/minMaxTimePlugin";
import {convertArrayToCSV} from "convert-array-to-csv";

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

            // Disable submit buttons for AJAX forms
            if (settings.nette.form) {
                disableFormSubmitButtons(settings.nette.form.get(0));
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
    $(document).on('submit', 'form', function(e) {
        disableFormSubmitButtons(this);
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
            },
            parseDate(dateStr, format) {
                var momentFormats = [format, "d.m.Y", "d.m.Y H:i:S"].map(x => flatpickrToMoment(x));
                return moment(dateStr, [...momentFormats , moment.ISO_8601], true).toDate()
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

        var dateFormat = $(this).attr('flatpickr_dateformat');
        if (dateFormat) {
            properties["dateFormat"] = dateFormat;
        }

        var minDate = $(this).attr('flatpickr_mindate');
        if (minDate) {
            properties["minDate"] = minDate;
        }
        var maxDate = $(this).attr('flatpickr_maxdate');
        if (maxDate) {
            properties["maxDate"] = maxDate;
        }

        var maxDatetime = $(this).attr('flatpickr_maxdatetime');
        if (maxDatetime) {
            maxDatetime = Date.parse(maxDatetime);

            properties['disable'] = [(date) => (date.valueOf() >= maxDatetime)]
            properties['plugins'] = [minMaxTimePlugin({
                getTimeLimits: () => {
                    return {
                        maxTime: maxDatetime
                    };
                }
            })]
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
    initDropdownSectionsMerger();
});

function flatpickrToMoment(format) {
    let rules = {
        'J': 'Do',
        'M': 'MMM',
        'm': 'MM',
        'i': 'mm',
        'S': 'ss',
        'd': 'DD',
        'Y': 'YYYY',
        'H': 'HH',
    }

    for (const [key, value] of Object.entries(rules)) {
        format = format.replace(key, value)
    }

    return format;
}

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

        let tags = $(this).attr('tags');
        if (tags === 'true') {
            config['tags'] = true
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
$(document).on('show.bs.collapse', '.collapse', function () {
    setTimeout(initSelect2, 0);
});
$(document).on('shown.bs.modal', '.modal', function () {
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
        plugins: {
            allowTagsFromPaste: {
                allowedTags: ['p', 'span', 'b', 'strong', 'i', 'em', 'strike', 'del', 'u', 'a', 'div']
            }
        }
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

function initDropdownSectionsMerger() {
    $('[data-dropdown-merge-sections]').each(function() {
        var dropdown = $(this);

        // key:sectionId, value:lastSectionItem
        var sections = {};

        dropdown.find('[data-dropdown-section]').each(function () {
            var section = $(this);
            var sectionId = section.data('dropdown-section');

            if (sections[sectionId] === undefined) {
                sections[sectionId] = section.nextUntil('[data-dropdown-section]').last();
                return;
            }

            var sectionItems = section.nextUntil('[data-dropdown-section]');
            section.remove();

            sections[sectionId].after(sectionItems);
            sections[sectionId] = sectionItems.last();
        });
    });
}

function disableFormSubmitButtons(form) {
    setTimeout(function() {
        let elements = form.querySelectorAll('[type="submit"], [data-submit="disable"]');
        elements.forEach(function(element) {
            element.disabled = true;
            element.classList.add('disabled');
        });
    }, 0); // Delay execution until after the form data is collected
}

window.crmAdmin = window.crmAdmin || {};

window.crmAdmin.downloadBlob = function (blob, filename) {
    var blobUrl = URL.createObjectURL(blob);

    var downloadLink = $('<a />', {
        href: blobUrl,
        download: filename,
        class: 'hidden',
        'aria-hidden': true,
    });
    downloadLink.appendTo($('body'));
    downloadLink[0].click();
    downloadLink.remove();

    URL.revokeObjectURL(blobUrl);
}

/**
 * Converts google visualization data to CSV (not a data table, just an input array/data)
 */
window.crmAdmin.googleVisualizationDataToCsv = function (data) {
    var modifiedData = data
        .map(function (row) {
            return row.map(function (cell) {
                if (cell instanceof Date) {
                    return moment(cell).format('YYYY-MM-DD');
                }

                if (Object.hasOwn(cell, 'label')) {
                    return cell.label;
                }

                return cell;
            });
        });

    return convertArrayToCSV(modifiedData);
}

/**
 * Returns Google Charts options for dark mode support
 * Merges the provided options with dark mode specific colors if dark mode is active
 */
window.crmAdmin.getGoogleChartsOptions = function (baseOptions) {
    var isDarkMode = document.body.classList.contains('dark-mode');

    if (!isDarkMode) {
        return baseOptions;
    }

    // Dark mode color configuration
    var darkModeOptions = {
        backgroundColor: '#1a1a1a',

        legend: {
            textStyle: {
                color: '#e0e0e0'
            }
        },

        titleTextStyle: {
            color: '#e0e0e0'
        },

        hAxis: {
            textStyle: {
                color: '#e0e0e0'
            },
            titleTextStyle: {
                color: '#e0e0e0'
            },
            gridlines: {
                color: '#3d3d3d'
            },
            minorGridlines: {
                color: '#2d2d2d'
            },
            baselineColor: '#3d3d3d'
        },

        vAxis: {
            textStyle: {
                color: '#e0e0e0'
            },
            titleTextStyle: {
                color: '#e0e0e0'
            },
            gridlines: {
                color: '#3d3d3d'
            },
            minorGridlines: {
                color: '#2d2d2d'
            },
            baselineColor: '#3d3d3d'
        },

        // Tooltip styling
        tooltip: {
            textStyle: {
                color: '#e0e0e0'
            }
        },

        // Annotation styling
        annotations: {
            textStyle: {
                color: '#e0e0e0'
            }
        }
    };

    // Deep merge function to combine objects
    function deepMerge(target, source) {
        for (var key in source) {
            if (source.hasOwnProperty(key)) {
                if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                    target[key] = target[key] || {};
                    deepMerge(target[key], source[key]);
                } else {
                    target[key] = source[key];
                }
            }
        }
        return target;
    }

    // Merge base options with dark mode options
    return deepMerge(JSON.parse(JSON.stringify(baseOptions)), darkModeOptions);
}
