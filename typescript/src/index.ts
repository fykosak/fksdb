import { app } from './app-collector';
import { eventAccommodation } from './events/accommodation/Index';
import { eventApplicationsTimeProgress } from './events/applications-time-progress/Index';
import { fyziklani } from './fyziklani/Index';
import { payment } from './payment/selectField/Index';

app.register(fyziklani);
app.register(eventAccommodation);
app.register(payment);
app.register(eventApplicationsTimeProgress);

app.run();
