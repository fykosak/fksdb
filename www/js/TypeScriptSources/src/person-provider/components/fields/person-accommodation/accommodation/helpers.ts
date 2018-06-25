import { formValueSelector } from 'redux-form';
import { IPersonSelector } from '../../../../../brawl-registration/middleware/price';
import { IStore } from '../../../../../brawl-registration/reducers';
import { IPrice } from '../../../../../shared/components/displays/price/interfaces';
import {
    IAccommodationItem,
    IPersonAccommodation,
} from './interfaces';

export const getAccommodationFromState = (FORM_NAME: string, state: IStore, ownProps: IPersonSelector): IPersonAccommodation => {
    const selector = formValueSelector(FORM_NAME);
    const participantsValue = selector(state, ownProps.type);
    if (participantsValue) {
        if (participantsValue.hasOwnProperty(ownProps.index)) {
            if (participantsValue[ownProps.index].hasOwnProperty('accommodation')) {
                return participantsValue[ownProps.index].accommodation;
            }
        }
    }
    return null;
};

export const getAccommodationPrice = (accommodationDef: IAccommodationItem[], accommodation: IPersonAccommodation): IPrice => {

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
                return value.accId === accommodation[date];
            })[0];
            if (selectedAcc) {
                sum.eur += +selectedAcc.price.eur;
                sum.kc += +selectedAcc.price.kc;
            }
        }
    }
    return sum;
};
