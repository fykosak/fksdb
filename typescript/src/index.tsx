import AjaxSubmit from '@apps/ajaxSubmit';
import ParticipantAcquaintance from '@apps/chart/participantAcquaintance';
import TotalPersons from '@apps/chart/totalPersons';
import Attendance from '@apps/events/attendance';
import ApplicationsTimeProgressChart from '@apps/events/charts/applicationsTimeProgress';
import { eventSchedule } from '@apps/events/schedule';
import TaskCodeApp from '@apps/fyziklani/submitForm';
import FyziklaniResultsPresentation from '@apps/fyziklaniResults/presentation';
import FyziklaniResultsStatistics from '@apps/fyziklaniResults/statistics';
import FyziklaniResultsTable from '@apps/fyziklaniResults/table';
import PaymentSelectField from '@apps/payment/selectField/components/selectField';
import PersonTimeline from '@apps/person';
import { appsCollector } from '@appsCollector/index';
import { mapRegister } from '@appsCollector/mapRegister';
import * as React from 'react';
import * as ReactDOM from 'react-dom';

appsCollector.register(eventSchedule);

mapRegister.registerActionsComponent('public.ajax-submit', AjaxSubmit);
mapRegister.registerActionsComponent('fyziklani.results.table', FyziklaniResultsTable);
mapRegister.registerActionsComponent('fyziklani.statistics.team', FyziklaniResultsStatistics, {mode: 'team'});
mapRegister.registerActionsComponent('fyziklani.statistics.task', FyziklaniResultsStatistics, {mode: 'task'});
mapRegister.registerActionsComponent('fyziklani.statistics.correlation', FyziklaniResultsStatistics, {mode: 'correlation'});
mapRegister.registerActionsComponent('fyziklani.results.presentation', FyziklaniResultsPresentation);

// tslint:disable-next-line:max-line-length
mapRegister.registerDataComponent('events.applications-time-progress.participants', ApplicationsTimeProgressChart, {accessKey: 'participants'});
mapRegister.registerDataComponent('events.applications-time-progress.teams', ApplicationsTimeProgressChart, {accessKey: 'teams'});
mapRegister.registerDataComponent('chart.total-person', TotalPersons);
mapRegister.registerDataComponent('person.detail.timeline', PersonTimeline);
mapRegister.registerDataComponent('chart.participant-acquaintance', ParticipantAcquaintance);

mapRegister.registerComponent('attendance.qr-code', Attendance);

mapRegister.register('payment.schedule-select', (element, reactId, rawData) => {
    const container = document.createElement('div');
    element.parentElement.appendChild(container);
    ReactDOM.render(<PaymentSelectField data={JSON.parse(rawData)} input={element}/>, container);
});
mapRegister.register('fyziklani.submit-form', (element, reactId, rawData, actions) => {
    const c = document.createElement('div');
    const {tasks, teams, availablePoints} = JSON.parse(rawData);
    element.appendChild(c);
    ReactDOM.render(<TaskCodeApp tasks={tasks} teams={teams} actions={actions}
                                 availablePoints={availablePoints}/>, c);
});

appsCollector.run();
