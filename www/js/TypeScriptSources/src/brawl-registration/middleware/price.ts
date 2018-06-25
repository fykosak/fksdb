import { formValueSelector } from 'redux-form';
import { IStore } from '../reducers';
import { IScheduleItem } from './iterfaces';
import { IPersonAccommodation } from '../../person-provider/components/fields/person-accommodation/accommodation/interfaces';

export interface IPrice {
    eur: number;
    kc: number;
}

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
    accessKey: string;
}

export interface IPersonStringSelectror {
    accessKey: string;
}

export const getScheduleFromState = (FORM_NAME: string, state: IStore, ownProps: IPersonSelector): boolean[] => {
    const selector = formValueSelector(FORM_NAME);
    const participantsValue = selector(state, ownProps.type);
    if (participantsValue) {
        if (participantsValue.hasOwnProperty(ownProps.index)) {
            if (participantsValue[ownProps.index].hasOwnProperty('schedule')) {
                return participantsValue[ownProps.index].schedule;
            }
        }
    }
    return null;
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

export const requireIdNumberFromAccommodation = (accommodation: IPersonAccommodation): string[] => {
    let requireIdNumber = false;
    const keys = [];
    for (const date in accommodation) {

        if (accommodation.hasOwnProperty(date)) {
            requireIdNumber = requireIdNumber || !!accommodation[date];
        }
    }
    if (requireIdNumber) {
        keys.push('Accommodation');
    }
    return keys;
};

export const requireIdNumberFromSchedule = (schedule: boolean[], scheduleDef: IScheduleItem[]): string[] => {
    let requireIdNumber = false;
    const keys = [];
    if (schedule) {
        schedule.forEach((scheduleValue, scheduleIndex) => {
            if (!scheduleValue) {
                return;
            }
            const selectedSchedule = scheduleDef.filter((value) => {
                return value.id === scheduleIndex;
            })[0];

            if (selectedSchedule && selectedSchedule.requireIdNumber) {
                requireIdNumber = true;
                keys.push(selectedSchedule.scheduleName);
            }
        });
    }
    return keys;
};
