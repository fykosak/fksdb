import { app } from './app-collector';
import { eventAccommodation } from './events/accommodation';
import { fyziklani } from './fyziklani';

app.register(fyziklani);
app.register(eventAccommodation);

app.run();
