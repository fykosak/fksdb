import { LocalizedString } from '@translator/translator';
import { Price } from 'FKSDB/Models/Payment/price';

export interface ModelScheduleItem {
    scheduleGroupId: number;
    price: Price;
    totalCapacity?: number;
    usedCapacity: number;
    scheduleItemId: number;
    label: LocalizedString;
    requireIdNumber: boolean;
    description: LocalizedString;
}
