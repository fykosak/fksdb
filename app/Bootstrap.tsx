import * as ReactDOM from 'react-dom';
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
import StatisticsComponent from './Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/StatisticsComponent';
import PointsEntryComponent from './Components/Controls/Fyziklani/Submit/PointsEntryComponent';
import TimelineComponent from './Components/Controls/Person/Detail/Timeline/TimelineComponent';
import { eventSchedule } from './Components/Forms/Controls/Schedule/ScheduleField';
import Renderer from 'vendor/fykosak/nette-frontend-component/src/Loader/Renderer';
import * as React from 'react';

import 'vendor/nette/forms/src/assets/netteForms.js';
import './Components/Forms/Controls/sqlConsole';
import './css/index.scss';
import EventModelComponent from 'FKSDB/Components/Charts/Event/Model/EventModelComponent';
import '@fortawesome/fontawesome-free/css/all.css'
import 'bootstrap/dist/js/bootstrap.bundle'
import { translator } from '@translator/translator';

const renderer = new Renderer();

renderer.hashMapLoader.register('event.schedule', eventSchedule);

renderer.hashMapLoader.registerActionsComponent('public.ajax-submit', AjaxSubmitComponent);
renderer.hashMapLoader.registerActionsComponent('fyziklani.results.table', ResultsTableComponent);
renderer.hashMapLoader.registerActionsComponent('fyziklani.statistics.team', StatisticsComponent, {mode: 'team'});
renderer.hashMapLoader.registerActionsComponent('fyziklani.statistics.task', StatisticsComponent, {mode: 'task'});
renderer.hashMapLoader.registerActionsComponent('fyziklani.statistics.correlation', StatisticsComponent, {mode: 'correlation'});
renderer.hashMapLoader.registerActionsComponent('fyziklani.results.presentation', ResultsPresentationComponent);
renderer.hashMapLoader.registerActionsComponent('fyziklani.submit-form', PointsEntryComponent);

renderer.hashMapLoader.registerDataComponent('chart.total-person', TotalPersonsChart);
renderer.hashMapLoader.registerDataComponent('chart.person.detail.timeline', TimelineComponent);

renderer.hashMapLoader.registerDataComponent('chart.contestants.per-series', PerSeriesChart);
renderer.hashMapLoader.registerDataComponent('chart.contestants.per-years', PerYearsChart);

renderer.hashMapLoader.registerDataComponent('chart.events.participants.time-progress', CommonChart, {accessKey: 'participants'});
renderer.hashMapLoader.registerDataComponent('chart.events.participants.acquaintance', ParticipantAcquaintanceChart);
renderer.hashMapLoader.registerDataComponent('chart.events.participants.time-geo', ParticipantsTimeGeoChart);

renderer.hashMapLoader.registerDataComponent('chart.events.teams.geo', TeamsGeoChart);
renderer.hashMapLoader.registerDataComponent('chart.events.teams.time-progress', CommonChart, {accessKey: 'teams'});
renderer.hashMapLoader.registerDataComponent('chart.events.application-ratio.geo', ApplicationRationGeoChart);

renderer.hashMapLoader.registerDataComponent('event.model.graph', EventModelComponent);


window.addEventListener('DOMContentLoaded', () => {

// @ts-ignore
    $.widget('fks.writeonlyInput', {
// default options
        options: {},
        _create: function () {

            const actElement = this.element as JQuery<HTMLInputElement>;
            if (actElement.attr('data-writeonly-enabled')) {
                return;
            }
            const originalValue = actElement.attr('data-writeonly-value');
            const originalLabel = actElement.attr('data-writeonly-label');

            const button = $('<i class="fa fa-times glyphicon glyphicon-remove"/>');
            const actualGroup = $('<div class="right-inner-addon"/>');

            // Workardound: .replaceWith breaks datepicker.
            const par = actElement.parent();
            const prev = actElement.prev();

            actualGroup.append(actElement);
            actualGroup.append(button);
            if (prev.length) {
                actualGroup.insertAfter(prev);
            } else {
                actualGroup.prependTo(par);
            }

            const overlayInput = actElement.clone();
            // @ts-ignore
            overlayInput.removeAttr('id', null).val('').attr('placeholder', originalLabel);
            overlayInput.removeClass('date').removeAttr('name');
            overlayInput.removeAttr('data-writeonly');
            overlayInput.removeAttr('data-nette-rules');
            overlayInput.removeAttr('required');
            // @ts-ignore
            overlayInput.attr('data-writeonly-overlay', true);
            overlayInput.insertAfter(actualGroup);

            function applyOverlay() {
                actualGroup.hide();
                actElement.val(originalValue);
                overlayInput.show();
            }

            function removeOverlay() {
                if (actElement.val() == originalValue) {
                    actElement.val('');
                }
                overlayInput.hide();
                actualGroup.show();
            }

            overlayInput.focus(() => {
                removeOverlay();
                actElement.focus();
            });

            button.click(() => applyOverlay());

            if (actElement.val() == originalValue) {
                applyOverlay();
            } else {
                removeOverlay();
            }
            actElement.data('writeonly-enabled', true);
        },
    });
    // @ts-ignore
    $('input[data-writeonly],input:data(writeonly)').writeonlyInput();

// @ts-ignore
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
            const container = this.element as JQuery<HTMLElement>;
            this.transformContainer(container, document.getElementById(container.attr('data-referenced-id')));
        },
        transformContainer: function (container: JQuery<HTMLElement>, refId: HTMLElement) {
            const elRefId = $(refId);
            const $searchInput = container.find('input[name*=\'' + this.options.searchMask + '\'][type!=\'hidden\']');
            const $compactValueInput = container.find('input[name*=\'' + this.options.compactValueMask + '\']');
            const $clearButton = container.find('input[type=\'submit\'][name*=\'' + this.options.clearMask + '\']');
            let compacted = null;
            //  const options = this.options;
            if (elRefId) {
                this.options.refId = elRefId;
            }

            function decompactifyContainer(): void {
                if (compacted !== null) {
                    compacted.hide();
                }
                container.show();
            }

            function createCompactField(label: string, value: string | number | string[]): JQuery<HTMLElement> {
                const compactGroup = document.createElement('div');
                const ReEl = () => {
                    return <fieldset className="col-12 bd-callout bd-callout-info" data-level="1">
                        <h4>{label}</h4>
                        <div className="form-group">
                            <div className="input-group">
                                <p className="form-control-plaintext"><span className="fa fa-user me-3"/>{value}</p>
                            </div>
                            <div className="input-group-append">
                                <button type="button"
                                        className="btn btn-outline-secondary"
                                        title={translator.getText('Upravit')}
                                        onClick={() => {
                                            decompactifyContainer();
                                        }}>
                                    <span className="fa fa-pen me-3"/>
                                    {translator.getText('Upravit')}
                                </button>
                                <button type="button"
                                        className="btn btn-outline-warning"
                                        title={translator.getText('Smazat')}
                                        onClick={() => {
                                            $clearButton.click();
                                        }}>
                                    <span className="fa fa-times me-3"/>
                                    {translator.getText('Smazat')}
                                </button>
                            </div>
                        </div>
                    </fieldset>;
                }
                ReactDOM.render(<ReEl/>, compactGroup);
                return $(compactGroup);
            }


            function compactifyContainer() {

                if (compacted === null) {
                    const label = container.find('> fieldset > h4').text();
                    const value = $compactValueInput.val();
                    compacted = createCompactField(label, value);
                    compacted.insertAfter(container);
                    //elContainer.find('legend').click(compactifyContainer);
                    //decorateClearButton(); //in original container
                }
                compacted.show();
                container.hide();
            }

            const hasAnyFields = container.find(':input[type!=\'hidden\'][disabled!=\'disabled\']').not($clearButton).filter(function () {
                return $(this).val() == '' && !$(this).attr('data-writeonly-overlay');
            });

            const hasErrors = container.find('.has-error');

            if ($searchInput.length) {
                // searchifyContainer();
            } else if ($clearButton.length && !(hasAnyFields.length || hasErrors.length)) {
                compactifyContainer();
            } else if ($clearButton.length && (hasAnyFields.length || hasErrors.length)) {
                // decorateClearButton();
            }
        },
    });
    // @ts-ignore
    $('[data-referenced]').referencedContainer();


    document.querySelectorAll('div.mergeSource').forEach((el) => {
        const field = document.getElementById(el.getAttribute('data-field'));
        field.addEventListener('click', () => {
            // @ts-ignore
            field.value = el.querySelector('.value').innerText;
        });
    });
    /*   document.querySelectorAll('.btn-outline-danger,.btn-danger').forEach((el) => {
           el.addEventListener('click', (event) => {
               if (window.confirm('O RLY?')) {
                   // @ts-ignore
                   el.trigger('click');
                   return;
               }
               event.preventDefault();
           })
       });*/

    // @ts-ignore
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
                    // @ts-ignore
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
                options.focus = () => false;
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

            // @ts-ignore
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

    renderer.run();
});
