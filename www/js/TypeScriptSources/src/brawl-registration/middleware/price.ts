import { IAccommodationItem } from '../components/accommodation';
import { formValueSelector } from 'redux-form';

export interface IPrice {
    eur: number;
    kc: number;
}

export const getPrice = (accommodationDef: IAccommodationItem[], acc): IPrice => {

    const sum = {
        eur: 0,
        kc: 0,
    };

    if (!acc) {
        return sum;
    }

    for (const date in acc) {
        if (acc.hasOwnProperty(date)) {
            const selectedAcc = accommodationDef.filter((value) => {
                return value.accId === acc[date];
            })[0];
            if (selectedAcc) {
                sum.eur += +selectedAcc.price.eur;
                sum.kc += +selectedAcc.price.kc;
            }
        }
    }
    return sum;
};

export interface IPersonSelector {
    type: string;
    index: number;
}

export interface IPersonAccommodation {
    acc?: any;
}

export const getAccommodationFromState = (FORM_NAME: string, state, ownProps: IPersonSelector): IPersonAccommodation => {
    const selector = formValueSelector(FORM_NAME);
    const participantsValue = selector(state, ownProps.type);
    if (participantsValue) {
        if (participantsValue.hasOwnProperty(ownProps.index)) {
            if (participantsValue[ownProps.index].hasOwnProperty('accommodation')) {
                return {
                    acc: participantsValue[ownProps.index].accommodation,
                };
            }
        }
    }
    return {};
};
