import { Price } from '@FKSDB/Model/Payment/price';
import { LocalizedString } from '@translator/translator';

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
