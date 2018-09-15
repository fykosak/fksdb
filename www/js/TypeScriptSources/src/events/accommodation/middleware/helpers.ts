import { IPrice } from '../../../shared/components/displays/price/interfaces';
import {
    IEventAccommodation,
    IPersonAccommodation,
} from './interfaces';

export const getAccommodationPrice = (accommodationDef: IEventAccommodation[], accommodation: IPersonAccommodation): IPrice => {

    const sum = {
        eur: 0,
        kc: 0,
    };

    if (!accommodation) {
        return sum;
    }

    for (const date in accommodation) {
        if (accommodation.hasOwnProperty(date)) {
            const selectedAcc = accommodationDef.filter((value) => {
                return value.eventAccommodationId === accommodation[date];
            })[0];
            if (selectedAcc) {
                sum.eur += +selectedAcc.price.eur;
                sum.kc += +selectedAcc.price.kc;
            }
        }
    }
    return sum;
};
