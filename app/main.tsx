import { createRoot } from 'react-dom/client';
import PerSeriesChart from './Components/Charts/Contestants/per-series-chart';
import PerYearsChart from './Components/Charts/Contestants/per-years-chart';
import TimeGeoChart from './Components/Charts/Event/Applications/time-geo-chart';
import AcquaintanceChart from './Components/Charts/Event/ParticipantAcquaintance/acquaintance-chart';
import TotalPersonsChart from './Components/Charts/total-persons-chart';
import AjaxSubmitComponent from './Components/Controls/Upload/AjaxSubmit/component';
import ResultsPresentation from './Components/Game/ResultsAndStatistics/Presentation/main';
import ResultsTable from './Components/Game/ResultsAndStatistics/Table/main';
import StatisticsComponent from './Components/Game/ResultsAndStatistics/Statistics/component';
import MainComponent from './Components/Game/Submits/Form/main-component';
import Renderer from 'vendor/fykosak/nette-frontend-component/src/Loader/renderer';
import * as React from 'react';
import 'vendor/nette/forms/src/assets/netteForms.js';
import './Components/Forms/Controls/sqlConsole';
import './css/index.scss';
import ModelChart from 'FKSDB/Components/Charts/Event/Model/model-chart';
import '@fortawesome/fontawesome-free/css/all.css'
import 'bootstrap/dist/js/bootstrap.bundle'
import { Translator } from '@translator/translator';
import Timeline from 'FKSDB/Components/Controls/Stalking/Timeline/timeline';
import ScheduleField from 'FKSDB/Components/Schedule/Input/schedule-field';
import ParticipantGeo from 'FKSDB/Components/Charts/Contestants/participant-geo';
import BarProgress from 'FKSDB/Components/Charts/Event/Applications/bar-progress';
import TimeProgress from 'FKSDB/Components/Charts/Event/Applications/time-progress';

const translator = new Translator();

const renderer = new Renderer();

renderer.hashMapLoader.register('schedule.group-container', (element, reactId, rawData) => {
    const container = document.createElement('div');
    element.parentElement.appendChild(container);
    if (element instanceof HTMLInputElement || element instanceof HTMLSelectElement) {
        const root = createRoot(container);
        root.render(<ScheduleField
            scheduleDef={JSON.parse(rawData)}
            input={element}
            translator={translator}/>);
        return true;
    }
    return false;
});

renderer.hashMapLoader.registerActionsComponent('public.ajax-submit', AjaxSubmitComponent, {translator});
renderer.hashMapLoader.registerActionsComponent('fyziklani.results.table', ResultsTable, {translator});
renderer.hashMapLoader.registerActionsComponent('fyziklani.statistics.team', StatisticsComponent, {
    mode: 'team',
    translator,
});
renderer.hashMapLoader.registerActionsComponent('fyziklani.statistics.task', StatisticsComponent, {
    mode: 'task',
    translator,
});
renderer.hashMapLoader.registerActionsComponent('fyziklani.statistics.correlation', StatisticsComponent, {
    mode: 'correlation',
    translator,
});
renderer.hashMapLoader.registerActionsComponent('fyziklani.results.presentation', ResultsPresentation, {
    event: 'fof',
    translator,
});
renderer.hashMapLoader.registerActionsComponent('ctyrboj.results.presentation', ResultsPresentation, {
    event: 'ctyrboj',
    translator,
});
renderer.hashMapLoader.registerActionsComponent('fyziklani.submit-form', MainComponent, {translator});
renderer.hashMapLoader.registerActionsComponent('ctyrboj.submit-form', MainComponent, {translator});

renderer.hashMapLoader.registerDataComponent('chart.total-person', TotalPersonsChart, {translator});
renderer.hashMapLoader.registerDataComponent('chart.person.detail.timeline', Timeline, {translator});

renderer.hashMapLoader.registerDataComponent('chart.contestants.per-series', PerSeriesChart, {translator});
renderer.hashMapLoader.registerDataComponent('chart.contestants.per-years', PerYearsChart, {translator});
renderer.hashMapLoader.registerDataComponent('chart.contestants.geo', ParticipantGeo);

renderer.hashMapLoader.registerDataComponent('chart.events.participants.acquaintance', AcquaintanceChart, {translator});
renderer.hashMapLoader.registerDataComponent('chart.events.participants.time-geo', TimeGeoChart, {translator});

renderer.hashMapLoader.registerDataComponent('chart.events.bar-progress', BarProgress, {translator});
renderer.hashMapLoader.registerDataComponent('chart.events.time-progress', TimeProgress, {translator});

renderer.hashMapLoader.registerDataComponent('event.model.graph', ModelChart, {translator});


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

            const button = $('<i class="fas fa-times"/>');
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

            overlayInput.removeAttr('id').val('').attr('placeholder', originalLabel);
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
            const $searchInput = container.find('input[name*=\'' + this.options.searchMask + '\'][type!=\'hidden\']');
            const $compactValueInput = container.find('input[name*=\'' + this.options.compactValueMask + '\']');
            const $clearButton = container.find('input[type=\'submit\'][name*=\'' + this.options.clearMask + '\']');
            let compacted = null;
            //  const options = this.options;
            if (refId) {
                this.options.refId = $(refId);
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
                                <p className="form-control-plaintext"><span className="fas fa-user me-3"/>{value}</p>
                            </div>
                            <div className="input-group-append">
                                <button
                                    type="button"
                                    className="btn btn-outline-secondary"
                                    title={translator.getText('Edit')}
                                    onClick={() => {
                                        decompactifyContainer();
                                    }}>
                                    <span className="fas fa-pen me-3"/>
                                    {translator.getText('Edit')}
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-outline-warning"
                                    title={translator.getText('Delete')}
                                    onClick={() => {
                                        $clearButton.click();
                                    }}>
                                    <span className="fas fa-times me-3"/>
                                    {translator.getText('Delete')}
                                </button>
                            </div>
                        </div>
                    </fieldset>;
                }
                const root = createRoot(compactGroup);
                root.render(<ReEl/>, compactGroup);
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

            const options: Record<string, unknown> = {};

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
                };
                options.minLength = 3;
            } else {
                const items = this.element.data('ac-items');
                options.source = (request, response) => {
                    const s = termFunction(request.term);
                    // @ts-ignore
                    response($.ui.autocomplete.filter(items, s));
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
