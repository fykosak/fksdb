import { appsCollector } from '@appsCollector';
import { eventAccommodation } from './apps/events/accommodation/';
import { eventApplicationsTimeProgress } from './apps/events/applicationsTimeProgress/';
import { fyziklani } from './apps/fyziklani/';
import { payment } from './apps/payment/selectField/';

appsCollector.register(fyziklani);
appsCollector.register(eventAccommodation);
appsCollector.register(payment);
appsCollector.register(eventApplicationsTimeProgress);

appsCollector.run();
