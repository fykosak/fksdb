import { ModelScheduleItem } from '@FKSDB/Model/ORM/Models/Schedule/ModelScheduleItem';
import { LocalizedString } from '@translator/Translator';

export interface ModelScheduleGroup {
    items: ModelScheduleItem[];
    scheduleGroupId: number;
    scheduleGroupType: ScheduleGroupType;
    label: LocalizedString;
    eventId: number;
    start: string;
    end: string;
}

export type ScheduleGroupType = 'accommodation' | 'dsef-group';
