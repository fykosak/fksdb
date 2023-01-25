import { LocalizedString } from '@translator/translator';
import { ModelScheduleItem } from 'FKSDB/Models/ORM/Models/Schedule/modelScheduleItem';

export interface ModelScheduleGroup {
    items: ModelScheduleItem[];
    scheduleGroupId: number;
    scheduleGroupType: ScheduleGroupType;
    name: LocalizedString;
    eventId: number;
    start: string;
    end: string;
}

export type ScheduleGroupType = 'accommodation' | 'dsef-group';
