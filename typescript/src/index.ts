import { appsCollector } from '@appsCollector';
import { eventApplicationsTimeProgress } from './apps/events/applicationsTimeProgress/';
import { eventSchedule } from './apps/events/schedule';
import { fyziklani } from './apps/fyziklani/';
import { payment } from './apps/payment/selectField/';
import { ajaxUpload } from "./ajaxUpload";

appsCollector.register(fyziklani);
appsCollector.register(payment);
appsCollector.register(eventApplicationsTimeProgress);
appsCollector.register(eventSchedule);
appsCollector.register(ajaxUpload);

appsCollector.run();
