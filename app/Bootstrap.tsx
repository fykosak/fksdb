import PerSeriesChartComponent from 'FKSDB/Components/Charts/Contestants/PerSeriesChartComponent';
import PerYearsChartComponent from 'FKSDB/Components/Charts/Contestants/PerYearsChartComponent';
import ApplicationRationGeoChart from 'FKSDB/Components/Charts/Event/Applications/ApplicationRationGeoChart';
import ParticipantsTimeGeoChart from 'FKSDB/Components/Charts/Event/Applications/ParticipantsTimeGeoChart';
import TeamsGeoChart from 'FKSDB/Components/Charts/Event/Applications/TeamsGeoChart';
import CommonChartComponent from 'FKSDB/Components/Charts/Event/ApplicationsTimeProgress/CommonChartComponent';
import ParticipantAcquaintanceChart from 'FKSDB/Components/Charts/Event/ParticipantAcquaintance/ParticipantAcquaintanceChart';
import TotalPersonsChartComponent from 'FKSDB/Components/Charts/TotalPersonsChartComponent';
import AjaxSubmitComponent from 'FKSDB/Components/Controls/AjaxSubmit/AjaxSubmitComponent';
import ResultsPresentationComponent from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/ResultsPresentation/ResultsPresentationComponent';
import ResultsTableComponent from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/ResultsTable/ResultsTableComponent';
import StatisticsComponent from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/StatisticsComponent';
import PointsEntryComponent from 'FKSDB/Components/Controls/Fyziklani/Submit/PointsEntryComponent';
import TimelineComponent from 'FKSDB/Components/Controls/Stalking/Timeline/TimelineComponent';
import { eventSchedule } from 'FKSDB/Components/Forms/Controls/Schedule/ScheduleField';
import Attendance from 'FKSDB/Models/FrontEnd/apps/events/attendance/Index';
import { appsLoader } from 'FKSDB/Models/FrontEnd/Loader/Loader';
import * as React from 'react';

appsLoader.register(eventSchedule);

appsLoader.hashMapLoader.registerActionsComponent('public.ajax-submit', AjaxSubmitComponent);
appsLoader.hashMapLoader.registerActionsComponent('fyziklani.results.table', ResultsTableComponent);
appsLoader.hashMapLoader.registerActionsComponent('fyziklani.statistics.team', StatisticsComponent, {mode: 'team'});
appsLoader.hashMapLoader.registerActionsComponent('fyziklani.statistics.task', StatisticsComponent, {mode: 'task'});
appsLoader.hashMapLoader.registerActionsComponent('fyziklani.statistics.correlation', StatisticsComponent, {mode: 'correlation'});
appsLoader.hashMapLoader.registerActionsComponent('fyziklani.results.presentation', ResultsPresentationComponent);
appsLoader.hashMapLoader.registerActionsComponent('fyziklani.submit-form', PointsEntryComponent);

appsLoader.hashMapLoader.registerDataComponent('chart.total-person', TotalPersonsChartComponent);
appsLoader.hashMapLoader.registerDataComponent('chart.person.detail.timeline', TimelineComponent);

appsLoader.hashMapLoader.registerDataComponent('chart.contestants.per-series', PerSeriesChartComponent);
appsLoader.hashMapLoader.registerDataComponent('chart.contestants.per-years', PerYearsChartComponent);

appsLoader.hashMapLoader.registerDataComponent('chart.events.participants.time-progress', CommonChartComponent, {accessKey: 'participants'});
appsLoader.hashMapLoader.registerDataComponent('chart.events.participants.acquaintance', ParticipantAcquaintanceChart);
appsLoader.hashMapLoader.registerDataComponent('chart.events.participants.time-geo', ParticipantsTimeGeoChart);

appsLoader.hashMapLoader.registerDataComponent('chart.events.teams.geo', TeamsGeoChart);
appsLoader.hashMapLoader.registerDataComponent('chart.events.teams.time-progress', CommonChartComponent, {accessKey: 'teams'});
appsLoader.hashMapLoader.registerDataComponent('chart.events.application-ratio.geo', ApplicationRationGeoChart);

appsLoader.hashMapLoader.registerComponent('attendance.qr-code', Attendance);

appsLoader.run();
