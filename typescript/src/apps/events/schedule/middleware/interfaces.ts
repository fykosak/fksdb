import { Price } from '@shared/components/displays/price/interfaces';
import { LocalizedString } from '@i18n/i18n';

export interface ScheduleItemDef {
    scheduleGroupId: number;
    price: Price;
    totalCapacity: number;
    usedCapacity: number;
    scheduleItemId: number;
    label: LocalizedString;
    requireIdNumber: boolean;
    description: LocalizedString;
}

export interface ScheduleGroupDef {
    items: ScheduleItemDef[];
    scheduleGroupId: number;
    scheduleGroupType: ScheduleGroupType;
    label: string;
    eventId: number;
    start: string;
    end: string;
}

export interface PersonAccommodation {
    [groupId: string]: number;
}

export type ScheduleGroupType = 'accommodation' | 'dsef-group';
