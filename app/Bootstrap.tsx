import PerSeriesChart from './Components/Charts/Contestants/PerSeriesChart';
import PerYearsChart from './Components/Charts/Contestants/PerYearsChart';
import ApplicationRationGeoChart from './Components/Charts/Event/Applications/ApplicationRationGeoChart';
import ParticipantsTimeGeoChart from './Components/Charts/Event/Applications/ParticipantsTimeGeoChart';
import TeamsGeoChart from './Components/Charts/Event/Applications/TeamsGeoChart';
import CommonChart from './Components/Charts/Event/ApplicationsTimeProgress/CommonChart';
import ParticipantAcquaintanceChart
    from './Components/Charts/Event/ParticipantAcquaintance/ParticipantAcquaintanceChart';
import TotalPersonsChart from './Components/Charts/TotalPersonsChart';
import AjaxSubmitComponent from './Components/Controls/AjaxSubmit/AjaxSubmitComponent';
import ResultsPresentationComponent
    from './Components/Controls/Fyziklani/ResultsAndStatistics/ResultsPresentation/ResultsPresentationComponent';
import ResultsTableComponent
    from './Components/Controls/Fyziklani/ResultsAndStatistics/ResultsTable/ResultsTableComponent';
import StatisticsComponent
    from './Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/StatisticsComponent';
import PointsEntryComponent from './Components/Controls/Fyziklani/Submit/PointsEntryComponent';
import TimelineComponent from './Components/Controls/Stalking/Timeline/TimelineComponent';
import { eventSchedule } from './Components/Forms/Controls/Schedule/ScheduleField';
import Attendance from './Models/FrontEnd/apps/events/attendance/Index';
import { appsLoader } from './Models/FrontEnd/Loader/Loader';
import * as React from 'react';

import '../vendor/nette/forms/src/assets/netteForms';
import '../libs/graph-dracula/vendor/raphael.js';
import '../libs/graph-dracula/lib/dracula_graffle.js';
import '../libs/graph-dracula/lib/dracula_graph.js';
import './Components/Forms/Controls/sqlConsole';
import './css/index.scss';

appsLoader.register(eventSchedule);

appsLoader.hashMapLoader.registerActionsComponent('public.ajax-submit', AjaxSubmitComponent);
appsLoader.hashMapLoader.registerActionsComponent('fyziklani.results.table', ResultsTableComponent);
appsLoader.hashMapLoader.registerActionsComponent('fyziklani.statistics.team', StatisticsComponent, {mode: 'team'});
appsLoader.hashMapLoader.registerActionsComponent('fyziklani.statistics.task', StatisticsComponent, {mode: 'task'});
appsLoader.hashMapLoader.registerActionsComponent('fyziklani.statistics.correlation', StatisticsComponent, {mode: 'correlation'});
appsLoader.hashMapLoader.registerActionsComponent('fyziklani.results.presentation', ResultsPresentationComponent);
appsLoader.hashMapLoader.registerActionsComponent('fyziklani.submit-form', PointsEntryComponent);

appsLoader.hashMapLoader.registerDataComponent('chart.total-person', TotalPersonsChart);
appsLoader.hashMapLoader.registerDataComponent('chart.person.detail.timeline', TimelineComponent);

appsLoader.hashMapLoader.registerDataComponent('chart.contestants.per-series', PerSeriesChart);
appsLoader.hashMapLoader.registerDataComponent('chart.contestants.per-years', PerYearsChart);

appsLoader.hashMapLoader.registerDataComponent('chart.events.participants.time-progress', CommonChart, {accessKey: 'participants'});
appsLoader.hashMapLoader.registerDataComponent('chart.events.participants.acquaintance', ParticipantAcquaintanceChart);
appsLoader.hashMapLoader.registerDataComponent('chart.events.participants.time-geo', ParticipantsTimeGeoChart);

appsLoader.hashMapLoader.registerDataComponent('chart.events.teams.geo', TeamsGeoChart);
appsLoader.hashMapLoader.registerDataComponent('chart.events.teams.time-progress', CommonChart, {accessKey: 'teams'});
appsLoader.hashMapLoader.registerDataComponent('chart.events.application-ratio.geo', ApplicationRationGeoChart);

appsLoader.hashMapLoader.registerComponent('attendance.qr-code', Attendance);

appsLoader.run();

$(function () {

    $.widget('fks.writeonlyInput', {
// default options
        options: {},
        _create: function () {
            const actualInput = $(this.element);
            if (actualInput.data('writeonly-enabled')) {
                return;
            }
            const originalValue = actualInput.data('writeonly-value');
            const originalLabel = actualInput.data('writeonly-label');

            const button = $('<i class="fa fa-times glyphicon glyphicon-remove"/>');
            const actualGroup = $('<div class="right-inner-addon"/>');

            // Workardound: .replaceWith breaks datepicker.
            const par = actualInput.parent();
            const prev = actualInput.prev();

            actualGroup.append(actualInput);
            actualGroup.append(button);
            if (prev.length) {
                actualGroup.insertAfter(prev);
            } else {
                actualGroup.prependTo(par);
            }

            const overlayInput = actualInput.clone();
            overlayInput.removeAttr('id', null).val('').attr('placeholder', originalLabel);
            overlayInput.removeClass('date').removeAttr('name');
            overlayInput.removeAttr('data-writeonly');
            overlayInput.removeAttr('data-nette-rules');
            overlayInput.removeAttr('required');
            overlayInput.attr('data-writeonly-overlay', true);
            overlayInput.insertAfter(actualGroup);

            function applyOverlay() {
                actualGroup.hide();
                actualInput.val(originalValue);
                overlayInput.show();
            }

            function removeOverlay() {
                if (actualInput.val() == originalValue) {
                    actualInput.val('');
                }
                overlayInput.hide();
                actualGroup.show();
            }

            overlayInput.focus(() => {
                removeOverlay();
                actualInput.focus();
            });

            button.click(() => {
                applyOverlay();
            });

            if (actualInput.val() == originalValue) {
                applyOverlay();
            } else {
                removeOverlay();
            }
            actualInput.data('writeonly-enabled', true);
        },
    });
    $('input[data-writeonly],input:data(writeonly)').writeonlyInput();

});

$(function () {

    $.widget('fks.referencedContainer', {
// default options
        options: {
            refId: null,
            valuePromise: '__promise',
            clearMask: '__clear',
            submitSearchMask: '__search',
            searchMask: '_c_search',
            compactValueMask: '_c_compact',
        },
        _create: function () {
            const elContainer = $(this.element);
            this.transformContainer(elContainer, $('#' + elContainer.data('referenced-id')));
        },
        transformContainer: function (elContainer, elRefId) {
            const $searchInput = elContainer.find('input[name*=\'' + this.options.searchMask + '\'][type!=\'hidden\']');
            const $compactValueInput = elContainer.find('input[name*=\'' + this.options.compactValueMask + '\']');
            const originalSearchButton = elContainer.find('input[type=\'submit\'][name*=\'' + this.options.submitSearchMask + '\']');
            const $clearButton = elContainer.find('input[type=\'submit\'][name*=\'' + this.options.clearMask + '\']');
            let compacted = null;
            const options = this.options;
            if (elRefId) {
                this.options.refId = elRefId;
            }

            const searchifyContainer = function () {

                // create search button
                const searchButton = $('<button class="input-group-append btn btn-secondary" type="button"><span class="fa fa-search"></span></button>');
                searchButton.click(function () {
                    originalSearchButton.click();
                });

                const searchInputGroup = $('<div class="input-group"/>');
                let elToReplace = $searchInput;
                if ($searchInput.data('uiElement')) {
                    elToReplace = $searchInput.data('uiElement');
                }

                // Workaround for broken replaceWith()
                //elToReplace.replaceWith(searchInputGroup);
                const par = elToReplace.parent();
                const prev = elToReplace.prev();
                if (prev.length) {
                    searchInputGroup.insertAfter(prev);
                } else {
                    searchInputGroup.prependTo(par);
                }

                searchInputGroup.append($searchInput);
                searchInputGroup.append(elToReplace);
                searchInputGroup.append(searchButton);

                // append handler
                $searchInput.change(function () {
                    originalSearchButton.click();
                });
                // promote search group in place of the container
                // let searchGroup = $searchInput.closest('.form-group');


                // searchGroup.children('.control-label').text(elContainer.find('legend').text());
                // searchGroup.attr('id', elContainer.attr('id'));
                // elContainer.attr('id', null);
                // elContainer.replaceWith(searchGroup);
                // elContainer.hide();
                return;

                // elContainer.appendTo(searchGroup);// we need the group to working form

                // ensure proper filling of the referenced id
                const writableFields = elContainer.find(':input[type!=\'hidden\'][disabled!=\'disabled\']').not($clearButton);
                writableFields.change(function () {
                    const filledFields = writableFields.filter(function () {
                        return $(this).val() != '';
                    });
                    if (filledFields.length > 0 && options.refId.val() == '') {
                        options.refId.val(options.valuePromise);
                    } else if (filledFields.length == 0 && options.refId.val() == options.valuePromise) {
                        options.refId.val('');
                    }
                });

            };

            function decompactifyContainer() {
                if (compacted !== null) {
                    compacted.hide();
                }
                elContainer.show();
            }

            function createCompactField(label, value) {
                const $compactGroup = $('<div class="form-group">\
        <label class="control-label"/>\
<div class="input-group"><p class="form-control-static form-control"/></div></div>');

                const elLabel = $compactGroup.find('label');
                elLabel.text(label);

                const elValue = $compactGroup.find('p.form-control-static');
                const $label = $('<span></span>');
                elValue.append('<span class="fa fa-user mr-3"></span>');
                $label.text(value);
                elValue.append($label);

                const $btnContainer = $('<div class="input-group-append"></div>');
                const $buttonEdit = $('<button type="button" class="btn btn-secondary" title="Upravit"><span class="fa fa-pencil"></span></button>');
                $buttonEdit.click(decompactifyContainer);

                const $buttonDel = $('<button type="button" class="btn btn-warning" title="Smazat"><span class="fa fa-times"></span></button>');
                $buttonDel.click(function () {
                    $clearButton.click();
                });
                $btnContainer.append($buttonEdit);
                $btnContainer.append($buttonDel);

                elValue.parent('.input-group').append($btnContainer);

                return $compactGroup;
            }


            function compactifyContainer() {

                if (compacted === null) {
                    const label = elContainer.find('> fieldset > h4').text();
                    const value = $compactValueInput.val();
                    compacted = createCompactField(label, value); //TODO clear button
                    compacted.insertAfter(elContainer);
                    compacted.find('.value').click(decompactifyContainer);
                    //elContainer.find('legend').click(compactifyContainer);
                    decorateClearButton(); //in original container
                }
                compacted.show();
                elContainer.hide();
            }

            function decorateClearButton() {
                const well = elContainer.children('.bd-callout');
                const buttonDel = $('<button type="button" class="btn btn-sm btn-warning clear-referenced" title="Smazat"><span class="fa fa-times"></span></button>');
                buttonDel.click(function () {
                    $clearButton.click();
                });
                $clearButton.closest('.btn-group').hide();
                buttonDel.prependTo(well);
            }

            const hasAnyFields = elContainer.find(':input[type!=\'hidden\'][disabled!=\'disabled\']').not($clearButton).filter(function () {
                return $(this).val() == '' && !$(this).attr('data-writeonly-overlay');
            });

            const hasErrors = elContainer.find('.has-error');

            if ($searchInput.length) {
                searchifyContainer();
            } else if ($clearButton.length && !(hasAnyFields.length || hasErrors.length)) {
                compactifyContainer();
            } else if ($clearButton.length && (hasAnyFields.length || hasErrors.length)) {
                decorateClearButton();
            }
        },
    });
    $('[data-referenced]').referencedContainer();

});
jQuery(function () {
    document.querySelectorAll('div.mergeSource').forEach((el) => {
        const field = document.getElementById(el.getAttribute('data-field'));
        field.addEventListener('click', () => {
            field.value = el.querySelector('.value').innerText;
        });
    });
});
$(document).ready(function () {
    $.widget('fks.enterSubmitForm', {
        _create: function () {
            this.update();
        },
        update: function () {
            const elForm = $(this.element);
            const elSubmit = elForm.find('input[data-submit-on=\'this\']');
            elForm.find('input').not(':data(submit-on-handled)')
                .data('submit-on-handled', true)
                .keypress(function (e) {
                    if (e.which == 13) {
                        elSubmit.click();
                        return false;
                    }
                });
        },
    });

    $('form[data-submit-on=\'enter\']').enterSubmitForm();
    document.querySelectorAll('.btn-danger').forEach((el) => {
        el.addEventListener('click', () => {
            if (window.confirm('O RLY?')) {
                el.trigger('click');
            }
        })
    });
    // TODO form buttons aren't checked

});
$(function () {
    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
    })
});

$(() => {
    const initRenderer = (r, node) => {
        const ellipse = r.ellipse(0, 0, 8, 8).attr({
            fill: '#000',
            stroke: '#000',
            'stroke-width': 0,
        });
        /* set DOM node ID */
        ellipse.node.id = node.label || node.id;
        return r.set().push(ellipse);
    };

    const terminatedRenderer = (r, node) => {
        const inner = r.ellipse(0, 0, 5, 5).attr({
            fill: '#000',
            stroke: '#000',
            'stroke-width': 0,
        });

        const outer = r.ellipse(0, 0, 10, 10).attr({
            fill: null,
            stroke: '#000',
            'stroke-width': 2,
        });
        /* set DOM node ID */
        inner.node.id = node.label || node.id;
        return r.set().push(inner).push(outer);
    };

    const componentId = 'graph-graphComponent';
    const component = document.getElementById(componentId);
    if (component) {
        const nodes = JSON.parse(component.getAttribute('data-nodes'));
        const edges = JSON.parse(component.getAttribute('data-edges'));

        const graph = new Graph();
        nodes.forEach((node) => {
            let render = null;
            switch (node.renderer) {
                case 'init':
                    render = initRenderer;
                    break;
                case 'terminated':
                    render = terminatedRenderer;
            }
            graph.addNode(node.id, {label: node.label, render});
        });
        edges.forEach((edge) => {
            let style = null;
            const labelStyle: Record<string, any> = {};
            let label = edge.label;
            if (edge.condition !== 1) {
                labelStyle.title = edge.condition;
                label = label + '*';
            }

            if (edge.target === 'cancelled') {
                style = '#ccc';
                labelStyle.stroke = '#ccc';
            }
            graph.addEdge(edge.source, edge.target, {
                directed: true,
                label, 'label-style':
                labelStyle,
                stroke: style,
            });
        });

        const layouter = new Graph.Layout.Spring(graph);
        layouter.layout();

        /* draw the graph using the RaphaelJS draw implementation */
        const renderer = new Graph.Renderer.Raphael(component, graph, $(component).width(), 600);
        renderer.draw();
    }
});
$(function () {
    $.widget('fks.autocomplete-select', $.ui.autocomplete, {
// default options
        options: {
            metaSuffix: '__meta',
        },
        _create: function () {
            const split = (val) => {
                return val.split(/,\s*/);
            }

            const extractLast = (term) => {
                return split(term).pop();
            }

            const multiSelect = this.element.data('ac-multiselect');
            const defaultValue = this.element.val();
            const defaultText = this.element.data('ac-default-value');

            const el = $('<input type="text"/>');
            el.attr('class', this.element.attr('class'));
            el.attr('disabled', this.element.attr('disabled'));
            this.element.replaceWith(el);
            this.element.hide();
            this.element.insertAfter(el);
            this.element.data('uiElement', el);

            // element to detect enabled JavaScript
            const metaEl = $('<input type="hidden" value="JS" />');
            // this should work both for array and scalar names
            const metaName = this.element.attr('name').replace(/(\[?)([^\[\]]+)(\]?)$/g, '$1$2' + this.options.metaSuffix + '$3');
            metaEl.attr('name', metaName);
            metaEl.insertAfter(el);

            this.element.data('autocomplete', el);
            if (defaultText) {
                if (typeof defaultText === 'string') {
                    el.val(defaultText);
                } else {
                    el.val(defaultText.join(', '));
                }
            }

            const cache = {}; //TODO move in better scope
            const labelCache = {};
            let termFunction = (arg) => {
                return arg;
            };
            // ensures default value is always suggested (needed for AJAX)
            const conservationFunction = (data) => {
                if (!defaultText) {
                    return data;
                }
                let found = false;
                for (const i in data) {
                    if (data[i].value == defaultValue) {
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    data.push({
                        label: defaultText,
                        value: defaultValue,
                    });
                }
                return data;
            };
            if (multiSelect) {
                termFunction = extractLast;
            }

            const options: Record<string, any> = {};

            if (this.element.data('ac-ajax')) {
                options.source = (request, response) => {
                    const term = termFunction(request.term);
                    if (term in cache) {
                        response(cache[term]);
                        return;
                    }
                    fetch(this.element.data('ac-ajax-url'), {
                            body: JSON.stringify({acQ: term}),
                            method: 'POST',
                        },
                    ).then((response) => {
                        return response.json();
                    }).then((jsonData) => {
                        const data = conservationFunction(jsonData);
                        cache[term] = data;
                        response(data);
                    });
                    /* $.getJSON(this.element.data('ac-ajax-url'), {acQ: term}, (data, status, xhr) => {
                         data = conservationFunction(data);
                         cache[term] = data;
                         response(data);
                     });*/
                };
                options.minLength = 3;
            } else {
                const items = this.element.data('ac-items');
                options.source = (request, response) => {
                    const s = termFunction(request.term);
                    response($.ui.autocomplete.filter(
                        items, s));
                };
                options.minLength = 3;
            }

            if (multiSelect) {
                options.select = (event, ui) => {
                    labelCache[ui.item.value] = ui.item.label;
                    if (this.element.val()) {
                        this.element.val(this.element.val() + ',' + ui.item.value);
                    } else {
                        this.element.val(ui.item.value);
                    }
                    el.val([].concat($.map(this.element.val().split(','), (arg) => {
                        return labelCache[arg];
                    }), ['']).join(', '));
                    return false;
                };
                options.focus = () => {
                    return false;
                };
            } else {
                options.select = (e, ui) => {
                    this.element.val(ui.item.value);
                    el.val(ui.item.label);
                    this.element.change();
                    return false;
                };
                options.focus = (e, ui) => {
                    this.element.val(ui.item.value);
                    el.val(ui.item.label);
                    return false;
                };
            }

            const acEl = el.autocomplete(options);

            const renderMethod = this.element.data('ac-render-method');
            if (renderMethod) {
                acEl.data('ui-autocomplete')._renderItem = (ul, item) => {
                    switch (renderMethod) {
                        case 'tags':
                            return $('<li>')
                                .append('<a>' + item.label + '<br>' + item.description + '</a>')
                                .appendTo(ul);
                        default:
                            return eval(renderMethod);
                    }

                };
            }
        },
    })

    $('input[data-ac]')['autocomplete-select']();
});
