import { availableLanguage, LangMap } from '@translator/translator';
import { Price } from 'FKSDB/Models/Payment/price';

export interface ScheduleItemModel {
    scheduleGroupId: number;
    price: Price;
    totalCapacity?: number;
    usedCapacity: number;
    scheduleItemId: number;
    label: LangMap<availableLanguage, string>;
    requireIdNumber: boolean;
    description: LangMap<availableLanguage, string>;
}
