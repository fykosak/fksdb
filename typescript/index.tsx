import Attendance from '@apps/events/attendance';
import { mapRegister } from '@appsCollector/mapRegister';
import AjaxSubmit from '@FKSDB/Components/Controls/AjaxSubmit/AjaxSubmit';
import PerSeriesChartComponent from '@FKSDB/Components/Controls/Chart/Contestants/PerSeriesChartComponent';
import PerYearsChartComponent from '@FKSDB/Components/Controls/Chart/Contestants/PerYearsChartComponent';
import PointsEntryComponent from '@FKSDB/Components/Controls/Fyziklani/Submit/PointsEntryComponent';
import { eventSchedule } from '@FKSDB/Components/Forms/Controls/Schedule/ScheduleField';
import * as React from 'react';
import CommonChartComponent from '../app/Components/Controls/Chart/Event/ApplicationsTimeProgress/CommonChartComponent';
import ParticipantAcquaintanceChartControl
    from '../app/Components/Controls/Chart/Event/ParticipantAcquaintanceChartControl';
import GeoChartComponent from '../app/Components/Controls/Chart/GeoCharts/GeoChartComponent';
import ParticipantsInTimeGeoChart from '../app/Components/Controls/Chart/GeoCharts/ParticipantsInTimeGeoChart';
import TotalPersonsChartComponent from '../app/Components/Controls/Chart/TotalPersonsChartComponent';
import TimelineComponent from '../app/Components/Controls/Stalking/Timeline/TimelineComponent';
import { appsCollector } from './appsCollector';
import ResultsTableComponent
    from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/ResultsTable/ResultsTableComponent';
import ResultsPresentationComponent
    from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/ResultsPresentation/ResultsPresentationComponent';
import StatisticsComponent
    from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/StatisticsComponent';

appsCollector.register(eventSchedule);

mapRegister.registerActionsComponent('public.ajax-submit', AjaxSubmit);
mapRegister.registerActionsComponent('fyziklani.results.table', ResultsTableComponent);
mapRegister.registerActionsComponent('fyziklani.statistics.team', StatisticsComponent, {mode: 'team'});
mapRegister.registerActionsComponent('fyziklani.statistics.task', StatisticsComponent, {mode: 'task'});
mapRegister.registerActionsComponent('fyziklani.statistics.correlation', StatisticsComponent, {mode: 'correlation'});
mapRegister.registerActionsComponent('fyziklani.results.presentation', ResultsPresentationComponent);
mapRegister.registerActionsComponent('fyziklani.submit-form', PointsEntryComponent);

// tslint:disable-next-line:max-line-length
mapRegister.registerDataComponent('events.applications-time-progress.participants', CommonChartComponent, {accessKey: 'participants'});
mapRegister.registerDataComponent('events.applications-time-progress.teams', CommonChartComponent, {accessKey: 'teams'});
mapRegister.registerDataComponent('chart.total-person', TotalPersonsChartComponent);
mapRegister.registerDataComponent('person.detail.timeline', TimelineComponent);
mapRegister.registerDataComponent('chart.participant-acquaintance', ParticipantAcquaintanceChartControl);
mapRegister.registerDataComponent('chart.contestants-per-series', PerSeriesChartComponent);
mapRegister.registerDataComponent('chart.contestants-per-years', PerYearsChartComponent);
mapRegister.registerDataComponent('chart.items-per-country-log', GeoChartComponent, {scaleType: 'log'});
mapRegister.registerDataComponent('chart.items-per-country-linear', GeoChartComponent, {scaleType: 'linear'});
mapRegister.registerDataComponent('chart.participants-in-time-geo', ParticipantsInTimeGeoChart);

mapRegister.registerComponent('attendance.qr-code', Attendance);

appsCollector.run();
