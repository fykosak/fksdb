import { availableLanguage, LangMap } from '@translator/translator';
import { ModelScheduleItem } from 'FKSDB/Models/ORM/Models/Schedule/modelScheduleItem';

export interface ModelScheduleGroup {
    items: ModelScheduleItem[];
    scheduleGroupId: number;
    scheduleGroupType: ScheduleGroupType;
    name: LangMap<availableLanguage, string>;
    eventId: number;
    start: string;
    end: string;
    registrationBegin: string | null;
    registrationEnd: string | null;
    modificationEnd: string | null;
}

export type ScheduleGroupType = 'accommodation' | 'dsef-group';
