import { app } from './app-collector';
import { eventAccommodation } from './events/accommodation';
import { eventApplicationsTimeProgress } from './events/applications-time-progress';
import { fyziklani } from './fyziklani';
import { payment } from './payment/selectField';

app.register(fyziklani);
app.register(eventAccommodation);
app.register(payment);
app.register(eventApplicationsTimeProgress);

app.run();
