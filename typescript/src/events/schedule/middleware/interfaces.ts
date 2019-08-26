import { Price } from '../../../shared/components/displays/price/interfaces';

export interface ScheduleItemDef {
    scheduleGroupId: number;
    price: Price;
    totalCapacity: number;
    usedCapacity: number;
    scheduleItemId: number;
    nameCs: string;
    nameEn: string;
    requireIdNumber: boolean;
}

export interface ScheduleGroupDef {
    items: ScheduleItemDef[];
    scheduleGroupId: number;
    scheduleGroupType: string;
    eventId: number;
    start: string;
    end: string;
}

export interface PersonAccommodation {
    [groupId: string]: number;
}
