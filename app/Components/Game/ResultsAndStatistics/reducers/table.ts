import { ACTION_SET_FILTER } from '../actions/table';
import { Filter } from '../Table/filter';
import { Action } from 'redux';

export interface State {
    filter: Filter | null;
}

const initialState: State = {
    filter: null,
};

export const table = (state: State = initialState, action: Action<string>): State => {

    switch (action.type) {
        case ACTION_SET_FILTER:
            return {
                ...state,
                filter: (action as { filter: Filter | null } & Action<string>).filter,
            };
        default:
            return state;
    }
};
