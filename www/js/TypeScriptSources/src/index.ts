import { app } from './app-collector';
import { eventAccommodation } from './events/accommodation';
import { fyziklani } from './fyziklani';
import { payment } from './payment/select-field';

app.register(fyziklani);
app.register(eventAccommodation);
app.register(payment);

app.run();
