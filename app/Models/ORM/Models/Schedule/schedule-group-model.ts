import {LangMap} from '@translator/translator';
import {ScheduleItemModel} from 'FKSDB/Models/ORM/Models/Schedule/schedule-item-model';

export interface ScheduleGroupModel {
    items: ScheduleItemModel[];
    scheduleGroupId: number;
    scheduleGroupType: ScheduleGroupType;
    name: LangMap<string, 'cs' | 'en'>;
    eventId: number;
    start: string;
    end: string;
    registrationBegin: string | null;
    registrationEnd: string | null;
    modificationEnd: string | null;
    requireIdNumber: boolean;
    paymentDeadline: string | null;
}

export type ScheduleGroupType =
    'accommodation'
    | 'accommodation_gender'
    | 'accommodation_teacher'
    | 'food'
    | 'visa'
    | 'vaccination_covid'
    | 'teacher_present'
    | 'weekend'
    | 'info'
    | 'excursion'
    | 'apparel'
    | 'transport'
    | 'ticket';


export interface Params {
    groupTime: boolean;
    groupLabel: boolean;
    capacity: boolean;
    price: boolean;
}

export const renderOptions = (type: ScheduleGroupType): Params => {
    switch (type) {
        case 'excursion':
            return {
                capacity: true,
                groupLabel: false,
                price: false,
                groupTime: false,
            };
        case 'accommodation':
            return {
                capacity: true,
                groupLabel: true,
                price: true,
                groupTime: false,
            }
        case 'vaccination_covid':
        case 'accommodation_teacher':
        case 'accommodation_gender':
        case 'visa':
        case 'teacher_present':
            return {
                capacity: false,
                groupLabel: false,
                price: false,
                groupTime: false,
            };
        case 'food':
            return {
                capacity: false,
                groupLabel: true,
                price: true,
                groupTime: false,
            };
        case 'weekend':
            return {
                capacity: true,
                groupLabel: true,
                price: true,
                groupTime: true,
            };
        case 'info':
        case 'apparel':
            return {
                capacity: false,
                groupLabel: true,
                price: false,
                groupTime: false,
            };
        case 'transport':
            return {
                capacity: false,
                groupLabel: true,
                price: false,
                groupTime: false,
            };
        case 'ticket':
            return {
                capacity: false,
                groupLabel: true,
                price: true,
                groupTime: false,
            };
    }
}
