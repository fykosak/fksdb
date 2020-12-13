import { ModelScheduleItem } from '@FKSDB/Model/ORM/Models/Schedule/modelScheduleItem';
import { LocalizedString } from '@translator/translator';

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
