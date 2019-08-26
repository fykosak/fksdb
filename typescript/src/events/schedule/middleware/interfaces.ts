import { Price } from '../../../shared/components/displays/price/interfaces';

export interface ScheduleItem {
    schedule_group_id: number;
    price: Price;
    totalCapacity: number;
    usedCapacity: number;
    schedule_item_id: number;
    name_cs: string;
    name_en: string;
    require_id_number: boolean;
}

export interface ScheduleGroup {
    items: ScheduleItem[];
    schedule_group_id: number;
    schedule_group_type: string;
    event_id: number;
    start: string;
    end: string;
}

export interface PersonAccommodation {
    [groupId: string]: number;
}
