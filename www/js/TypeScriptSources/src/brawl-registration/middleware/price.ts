import { formValueSelector } from 'redux-form';
import {
    IAccommodationItem,
    IScheduleItem,
} from './iterfaces';
import { IStore } from '../reducers';

export interface IPrice {
    eur: number;
    kc: number;
}

export const getAccommodationPrice = (accommodationDef: IAccommodationItem[], acc): IPrice => {

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

export const getSchedulePrice = (scheduleDef: IScheduleItem[], schedule: boolean[]): IPrice => {
    const sum = {
        eur: 0,
        kc: 0,
    };

    if (!schedule) {
        return sum;
    }
    schedule.forEach((scheduleValue, scheduleIndex) => {
        if (!scheduleValue) {
            return;
        }
        const selectedSchedule = scheduleDef.filter((value) => {
            return value.id === scheduleIndex;
        })[0];
        if (selectedSchedule && selectedSchedule.price) {
            sum.eur += +selectedSchedule.price.eur;
            sum.kc += +selectedSchedule.price.kc;
        }
    });
    return sum;
};

export interface IPersonSelector {
    type: string;
    index: number;
}

export interface IPersonAccommodation {
    accommodation?: any;
}

export const getAccommodationFromState = (FORM_NAME: string, state, ownProps: IPersonSelector): IPersonAccommodation => {
    const selector = formValueSelector(FORM_NAME);
    const participantsValue = selector(state, ownProps.type);
    if (participantsValue) {
        if (participantsValue.hasOwnProperty(ownProps.index)) {
            if (participantsValue[ownProps.index].hasOwnProperty('accommodation')) {
                return {
                    accommodation: participantsValue[ownProps.index].accommodation,
                };
            }
        }
    }
    return {};
};

export interface IPersonSchedule {
    schedule?: any;
}

export const getScheduleFromState = (FORM_NAME: string, state, ownProps: IPersonSelector): IPersonSchedule => {
    const selector = formValueSelector(FORM_NAME);
    const participantsValue = selector(state, ownProps.type);
    if (participantsValue) {
        if (participantsValue.hasOwnProperty(ownProps.index)) {
            if (participantsValue[ownProps.index].hasOwnProperty('schedule')) {
                return {
                    schedule: participantsValue[ownProps.index].schedule,
                };
            }
        }
    }
    return {};
};

export const getParticipantValues = (FORM_NAME: string, state: IStore, ownProps: IPersonSelector) => {
    const selector = formValueSelector(FORM_NAME);
    const participantsValue = selector(state, ownProps.type);
    if (participantsValue) {
        if (participantsValue.hasOwnProperty(ownProps.index)) {
            return participantsValue[ownProps.index];
        }
    }
    return {};
};
