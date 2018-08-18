import {
    ACTION_ADD_FILTER,
    ACTION_REMOVE_FILTER,
    ACTION_SET_AUTO_SWITCH,
    ACTION_SET_FILTER,
    ACTION_SET_NEXT_TABLE_FILTER,
    IActionWithFilter,
} from '../actions/table-filter';
import { Filter } from '../components/results/filter/filter';

export interface IFyziklaniTableFilterState {
    filters: Filter[];
    autoSwitch: boolean;
    index: number;
}

const setNextFilter = (state: IFyziklaniTableFilterState): IFyziklaniTableFilterState => {
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

const addFilter = (state: IFyziklaniTableFilterState, action: IActionWithFilter): IFyziklaniTableFilterState => {
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

const removeFilter = (state: IFyziklaniTableFilterState, action: IActionWithFilter): IFyziklaniTableFilterState => {
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

const setFilter = (state: IFyziklaniTableFilterState, action: IActionWithFilter): IFyziklaniTableFilterState => {
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

const setAutoSwitch = (state: IFyziklaniTableFilterState, action): IFyziklaniTableFilterState => {
    return {
        ...state,
        autoSwitch: action.state,

    };
};

// filters: action.state ? state.filters : [],
// index: action.state ? state.index : 0,

const initialState: IFyziklaniTableFilterState = {
    autoSwitch: false,
    filters: [],
    index: 0,
};

export const fyziklaniTableFilter = (state: IFyziklaniTableFilterState = initialState, action): IFyziklaniTableFilterState => {

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
