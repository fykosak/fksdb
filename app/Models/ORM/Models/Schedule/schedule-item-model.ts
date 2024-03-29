import { LangMap } from '@translator/translator';
import { Price } from 'FKSDB/Models/Payment/price';

export interface ScheduleItemModel {
    scheduleGroupId: number;
    price: Price;
    totalCapacity?: number;
    usedCapacity: number;
    scheduleItemId: number;
    name: LangMap<string, 'cs' | 'en'>;
    requireIdNumber: boolean;
    description: LangMap<string, 'cs' | 'en'>;
    available: boolean;
}
