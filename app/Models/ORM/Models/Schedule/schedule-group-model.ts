import { LangMap } from '@translator/translator';
import { ScheduleItemModel } from 'FKSDB/Models/ORM/Models/Schedule/schedule-item-model';

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
}

export type ScheduleGroupType = 'accommodation' | 'dsef-group';
