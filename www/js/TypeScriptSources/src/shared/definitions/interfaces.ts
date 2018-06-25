import {
    IPersonDefinition,
    IScheduleItem,
} from '../../brawl-registration/middleware/iterfaces';
import { IAccommodationItem } from '../../person-provider/components/fields/person-accommodation/accommodation/interfaces';

export interface IDefinitionsState {
    accommodation?: IAccommodationItem[];
    schedule?: IScheduleItem[];
    persons?: IPersonDefinition[];
    studyYears?: string[][];
}
