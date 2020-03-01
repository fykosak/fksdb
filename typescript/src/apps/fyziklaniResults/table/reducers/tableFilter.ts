import { FilterAction } from '../actions/filterAction';
import { ACTION_SET_NEXT_TABLE_FILTER } from '../actions/setnextFilter';
import {
    ACTION_ADD_FILTER,
    ACTION_REMOVE_FILTER,
    ACTION_SET_AUTO_SWITCH,
    ACTION_SET_FILTER,
} from '../actions/tableFilter';
import { Filter } from '../middleware/filters/filter';

export interface State {
    filters: Filter[];
    autoSwitch: boolean;
    index: number;
}

const setNextFilter = (state: State): State => {
    let {index} = state;
    const {filters} = state;
    index++;
    if (index >= filters.length) {
        index = 0;
    }
    return {
        ...state,
        index,
    };
};

const addFilter = (state: State, action: FilterAction): State => {
    const {filter} = action;
    const {filters} = state;
    const isIn = filters.some((actualFilters) => {
        return actualFilters.same(filter);
    });
    const newFilters = [...filters];
    if (!isIn) {
        newFilters.push(filter);
    }
    return {
        ...state,
        filters: [...newFilters],
    };
};

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

const setAutoSwitch = (state: State, action): State => {
    return {
        ...state,
        autoSwitch: action.state,

    };
};

const initialState: State = {
    autoSwitch: false,
    filters: [],
    index: 0,
};

export const fyziklaniTableFilter = (state: State = initialState, action): State => {

    switch (action.type) {
        case ACTION_ADD_FILTER:
            return addFilter(state, action);
        case ACTION_REMOVE_FILTER:
            return removeFilter(state, action);
        case ACTION_SET_NEXT_TABLE_FILTER:
            return setNextFilter(state);
        case ACTION_SET_AUTO_SWITCH:
            return setAutoSwitch(state, action);
        case ACTION_SET_FILTER:
            return setFilter(state, action);
        default:
            return state;
    }
};
