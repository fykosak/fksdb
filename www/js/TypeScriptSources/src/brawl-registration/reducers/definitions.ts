import { ACTION_SET_DEFINITIONS } from '../actions/definitions';

import {
    IAccommodationItem,
    IPersonDefinition,
    IScheduleItem,
} from '../middleware/iterfaces';

export interface IDefinitionsState {
    accommodation?: IAccommodationItem[];
    schedule?: IScheduleItem[];
    persons?: IPersonDefinition[];
    studyYears?: string[][];
}

const setDefinitions = (state: IDefinitionsState, action): IDefinitionsState => {
    return {
        ...state,
        ...action.data,
    };
};

export const definitions = (state: IDefinitionsState = {}, event): IDefinitionsState => {
    switch (event.type) {
        case ACTION_SET_DEFINITIONS:
            return setDefinitions(state, event);
        default:
            return state;

    }
};
