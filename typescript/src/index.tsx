import AjaxSubmit from '@apps/ajaxSubmit';
import ApplicationsTimeProgressChart from '@apps/chart/applicationsTimeProgress';
import ContestantsPerSeries from '@apps/chart/contestantsPerSeries';
import ContestantsPerYears from '@apps/chart/contestantsPerYears';
import ItemsPerCountryLinear from '@apps/chart/itemsPerCountryLinear';
import ItemsPerCountryLog from '@apps/chart/itemsPerCountryLog';
import ParticipantAcquaintance from '@apps/chart/participantAcquaintance';
import PersonTimeline from '@apps/chart/personTimeLine';
import TotalPersons from '@apps/chart/totalPersons';
import Attendance from '@apps/events/attendance';
import { eventSchedule } from '@apps/events/schedule';
import TaskCodeApp from '@apps/fyziklani/submitForm/components';
import FyziklaniResultsPresentation from '@apps/fyziklaniResults/presentation';
import FyziklaniResultsStatistics from '@apps/fyziklaniResults/statistics';
import FyziklaniResultsTable from '@apps/fyziklaniResults/table';
import { appsCollector } from '@appsCollector/index';
import { mapRegister } from '@appsCollector/mapRegister';
import * as React from 'react';
import ParticipantsInTimeGeoChart from '@apps/chart/participantsInTimeGeoChart';

appsCollector.register(eventSchedule);

mapRegister.registerActionsComponent('public.ajax-submit', AjaxSubmit);
mapRegister.registerActionsComponent('fyziklani.results.table', FyziklaniResultsTable);
mapRegister.registerActionsComponent('fyziklani.statistics.team', FyziklaniResultsStatistics, {mode: 'team'});
mapRegister.registerActionsComponent('fyziklani.statistics.task', FyziklaniResultsStatistics, {mode: 'task'});
mapRegister.registerActionsComponent('fyziklani.statistics.correlation', FyziklaniResultsStatistics, {mode: 'correlation'});
mapRegister.registerActionsComponent('fyziklani.results.presentation', FyziklaniResultsPresentation);
mapRegister.registerActionsComponent('fyziklani.submit-form', TaskCodeApp);

// tslint:disable-next-line:max-line-length
mapRegister.registerDataComponent('events.applications-time-progress.participants', ApplicationsTimeProgressChart, {accessKey: 'participants'});
mapRegister.registerDataComponent('events.applications-time-progress.teams', ApplicationsTimeProgressChart, {accessKey: 'teams'});
mapRegister.registerDataComponent('chart.total-person', TotalPersons);
mapRegister.registerDataComponent('person.detail.timeline', PersonTimeline);
mapRegister.registerDataComponent('chart.participant-acquaintance', ParticipantAcquaintance);
mapRegister.registerDataComponent('chart.contestants-per-series', ContestantsPerSeries);
mapRegister.registerDataComponent('chart.contestants-per-years', ContestantsPerYears);
mapRegister.registerDataComponent('chart.items-per-country-log', ItemsPerCountryLog);
mapRegister.registerDataComponent('chart.items-per-country-linear', ItemsPerCountryLinear);
mapRegister.registerDataComponent('chart.participants-in-time-geo', ParticipantsInTimeGeoChart);

mapRegister.registerComponent('attendance.qr-code', Attendance);

appsCollector.run();
