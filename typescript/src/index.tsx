import AjaxUpload from '@apps/ajaxUpload/components/item';
import ParticipantAcquaintance from '@apps/chart/participantAcquaintance';
import TotalPersons from '@apps/chart/totalPersons';
import ApplicationsTimeProgressChart from '@apps/events/applicationsTimeProgress/components';
import Attendance from '@apps/events/attendance';
import { eventSchedule } from '@apps/events/schedule';
import TaskCodeApp from '@apps/fyziklani/submitForm/components';
import Presentation from '@apps/fyziklaniResults/presentation/components';
import Statistics from '@apps/fyziklaniResults/statistics/components';
import Table from '@apps/fyziklaniResults/table/components';
import PaymentSelectField from '@apps/payment/selectField/components/selectField';
import DetailTimeline from '@apps/person';
import { appsCollector, mapRegister } from '@appsCollector/index';
import * as React from 'react';
import * as ReactDOM from 'react-dom';

appsCollector.register(eventSchedule);

mapRegister.register('public.ajax-upload', (element, reactId, rawData, actions) => {
    ReactDOM.render(<AjaxUpload data={JSON.parse(rawData)} actions={actions}/>, element);
});
mapRegister.register('events.applications-time-progress.participants', (element, reactId, rawData) => {
    ReactDOM.render(<ApplicationsTimeProgressChart data={JSON.parse(rawData)} accessKey={'participants'}/>, element);
});
mapRegister.register('events.applications-time-progress.teams', (element, reactId, rawData) => {
    ReactDOM.render(<ApplicationsTimeProgressChart data={JSON.parse(rawData)} accessKey={'teams'}/>, element);
});

mapRegister.register('chart.participant-acquaintance', (element, reactId, rawData) => {
    ReactDOM.render(<ParticipantAcquaintance data={JSON.parse(rawData)}/>, element);
});
mapRegister.register('chart.total-person', (element, reactId, rawData) => {
    ReactDOM.render(<TotalPersons data={JSON.parse(rawData)}/>, element);
});

mapRegister.register('payment.schedule-select', (element, reactId, rawData) => {
    const items = JSON.parse(rawData);
    const container = document.createElement('div');
    element.parentElement.appendChild(container);
    if (!(element instanceof HTMLInputElement)) {
        return false;
    }
    element.style.display = 'none';
    ReactDOM.render(<PaymentSelectField items={items} input={element}/>, container);
});
mapRegister.register('fyziklani.submit-form', (element, reactId, rawData, actions) => {
    const c = document.createElement('div');
    const {tasks, teams, availablePoints} = JSON.parse(rawData);
    element.appendChild(c);
    ReactDOM.render(<TaskCodeApp tasks={tasks} teams={teams} actions={actions}
                                 availablePoints={availablePoints}/>, c);
});

mapRegister.register('fyziklani.statistics.team', (element, reactId, rawData, actions) => {
    ReactDOM.render(<Statistics mode={'team'} actions={actions}/>, element);
});
mapRegister.register('fyziklani.statistics.task', (element, reactId, rawData, actions) => {
    ReactDOM.render(<Statistics mode={'task'} actions={actions}/>, element);
});
mapRegister.register('fyziklani.statistics.correlation', (element, reactId, rawData, actions) => {
    ReactDOM.render(<Statistics mode={'correlation'} actions={actions}/>, element);
});
mapRegister.register('fyziklani.results.presentation', (element, reactId, rawData, actions) => {
    ReactDOM.render(<Presentation actions={actions}/>, element);
});
mapRegister.register('fyziklani.results.table', (element, reactId, rawData, actions) => {
    ReactDOM.render(<Table actions={actions}/>, element);
});

mapRegister.register('attendance.qr-code', (element) => {
    ReactDOM.render(<Attendance/>, element);
});
mapRegister.register('person.detail.timeline', (element, reactId, rawData) => {
    const c = document.createElement('div');
    element.appendChild(c);
    const data = JSON.parse(rawData);
    ReactDOM.render(<DetailTimeline data={data}/>, c);
});

appsCollector.run();
