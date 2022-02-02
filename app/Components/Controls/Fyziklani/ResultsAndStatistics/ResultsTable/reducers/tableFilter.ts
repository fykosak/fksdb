import {
    ACTION_REMOVE_FILTER,
    ACTION_SET_FILTER,
    FilterAction,
} from '../actions';
import { Filter } from '../filter';

export interface State {
    filters: Filter[];
    autoSwitch: boolean;
    index: number;
}

const removeFilter = (state: State, action: FilterAction): State => {
    const {filter} = action;
    const {filters} = state;
    const newFilters = filters.filter((actualFilters) => {
        return !actualFilters.same(filter);
    });

    return {
        ...state,
        filters: [...newFilters],
    };
};

const setFilter = (state: State, action: FilterAction): State => {
    const {filter} = action;
    const {filters} = state;

    const isIn = filters.some((actualFilters) => {
        return actualFilters.same(filter);
    });
    if (isIn) {
        return {
            ...state,
            autoSwitch: false,
            filters: [],
            index: 0,
        };
    }

    return {
        ...state,
        autoSwitch: false,
        filters: [filter],
        index: 0,
    };
};

const initialState: State = {
    autoSwitch: false,
    filters: [],
    index: 0,
};

export const fyziklaniTableFilter = (state: State = initialState, action): State => {

    switch (action.type) {
        case ACTION_REMOVE_FILTER:
            return removeFilter(state, action);
        case ACTION_SET_FILTER:
            return setFilter(state, action);
        default:
            return state;
    }
};
