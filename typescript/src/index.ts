import { appsCollector } from '@appsCollector';
import { eventAccommodation } from './apps/events/accommodation/';
import { eventApplicationsTimeProgress } from './apps/events/applicationsTimeProgress/';
import { fyziklani } from './apps/fyziklani/';
import { payment } from './apps/payment/selectField/';
import { eventSchedule } from './apps/events/schedule';

appsCollector.register(fyziklani);
appsCollector.register(eventAccommodation);
appsCollector.register(payment);
appsCollector.register(eventApplicationsTimeProgress);
appsCollector.register(eventSchedule);

appsCollector.run();
