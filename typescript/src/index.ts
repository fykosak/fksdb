import { appsCollector, mapRegister } from '@appsCollector';
import { charts } from './apps/chart';
import { eventApplicationsTimeProgress } from './apps/events/applicationsTimeProgress/';
import { eventSchedule } from './apps/events/schedule';
import { fyziklani } from './apps/fyziklani/';
import { payment } from './apps/payment/selectField/';

appsCollector.register(eventApplicationsTimeProgress);
appsCollector.register(eventSchedule);
charts();
payment();
fyziklani();
appsCollector.run();
