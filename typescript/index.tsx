import AjaxSubmit from '@apps/ajaxSubmit';
import PersonTimeline from '@apps/chart/personTimeLine';
import Attendance from '@apps/events/attendance';
import { eventSchedule } from '@apps/events/schedule';
import TaskCodeApp from '@apps/fyziklani/submitForm/components';
import FyziklaniResultsPresentation from '@apps/fyziklaniResults/presentation';
import FyziklaniResultsStatistics from '@apps/fyziklaniResults/statistics';
import FyziklaniResultsTable from '@apps/fyziklaniResults/table';
import { mapRegister } from '@appsCollector/mapRegister';
import * as React from 'react';
import ContestantsPerSeries from '../app/Components/Controls/Chart/ContestantsPerSeries/contestantsPerSeries';
import ContestantsPerYears from '../app/Components/Controls/Chart/ContestantsPerSeries/contestantsPerYears';
import ChartComponent from '../app/Components/Controls/Chart/Event/ApplicationsTimeProgress/ChartComponent';
import ParticipantAcquaintanceChartControl
    from '../app/Components/Controls/Chart/Event/ParticipantAcquaintanceChartControl';
import GeoChartComponent from '../app/Components/Controls/Chart/GeoCharts/GeoChartComponent';
import ParticipantsInTimeGeoChart from '../app/Components/Controls/Chart/GeoCharts/ParticipantsInTimeGeoChart';
import TotalPersonsChartComponent from '../app/Components/Controls/Chart/TotalPersonsChartComponent';
import { appsCollector } from './appsCollector';

appsCollector.register(eventSchedule);

mapRegister.registerActionsComponent('public.ajax-submit', AjaxSubmit);
mapRegister.registerActionsComponent('fyziklani.results.table', FyziklaniResultsTable);
mapRegister.registerActionsComponent('fyziklani.statistics.team', FyziklaniResultsStatistics, {mode: 'team'});
mapRegister.registerActionsComponent('fyziklani.statistics.task', FyziklaniResultsStatistics, {mode: 'task'});
mapRegister.registerActionsComponent('fyziklani.statistics.correlation', FyziklaniResultsStatistics, {mode: 'correlation'});
mapRegister.registerActionsComponent('fyziklani.results.presentation', FyziklaniResultsPresentation);
mapRegister.registerActionsComponent('fyziklani.submit-form', TaskCodeApp);

// tslint:disable-next-line:max-line-length
mapRegister.registerDataComponent('events.applications-time-progress.participants', ChartComponent, {accessKey: 'participants'});
mapRegister.registerDataComponent('events.applications-time-progress.teams', ChartComponent, {accessKey: 'teams'});
mapRegister.registerDataComponent('chart.total-person', TotalPersonsChartComponent);
mapRegister.registerDataComponent('person.detail.timeline', PersonTimeline);
mapRegister.registerDataComponent('chart.participant-acquaintance', ParticipantAcquaintanceChartControl);
mapRegister.registerDataComponent('chart.contestants-per-series', ContestantsPerSeries);
mapRegister.registerDataComponent('chart.contestants-per-years', ContestantsPerYears);
mapRegister.registerDataComponent('chart.items-per-country-log', GeoChartComponent, {scaleType: 'log'});
mapRegister.registerDataComponent('chart.items-per-country-linear', GeoChartComponent, {scaleType: 'linear'});
mapRegister.registerDataComponent('chart.participants-in-time-geo', ParticipantsInTimeGeoChart);

mapRegister.registerComponent('attendance.qr-code', Attendance);

appsCollector.run();
