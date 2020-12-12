import { Price } from '@FKSDB/Model/Payment/Price';
import { LocalizedString } from '@translator/Translator';

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
