import { appsCollector } from '@appsCollector';
import { charts } from './apps/chart';
import { eventApplicationsTimeProgress } from './apps/events/applicationsTimeProgress/';
import { eventSchedule } from './apps/events/schedule';
import { fyziklani } from './apps/fyziklani/';
import { fyziklaniResults } from './apps/fyziklaniResults';
import { payment } from './apps/payment/selectField/';

appsCollector.register(eventSchedule);
eventApplicationsTimeProgress();
charts();
payment();
fyziklani();
fyziklaniResults();

appsCollector.run();
