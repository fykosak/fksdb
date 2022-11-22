import { ACTION_SET_FILTER } from '../actions';
import { Filter } from '../filter';

export interface State {
    filter: Filter | null;
}

const initialState: State = {
    filter: null,
};

export const tableFilter = (state: State = initialState, action): State => {

    switch (action.type) {
        case ACTION_SET_FILTER:
            return {
                ...state,
                filter: action.filter,
            };
        default:
            return state;
    }
};
