import { Filter } from '../components/results/filter/filter';

export const ACTION_SET_NEXT_TABLE_FILTER = '@@fyziklani/ACTION_SET_NEXT_TABLE_FILTER';

export const setNextFilter = () => {
    return {
        type: ACTION_SET_NEXT_TABLE_FILTER,
    };
};

export const ACTION_ADD_FILTER = '@@fyziklani/ACTION_ADD_FILTER';

export const addFilter = (filter: Filter) => {
    return {
        filter,
        type: ACTION_ADD_FILTER,
    };
};

export const ACTION_REMOVE_FILTER = '@@fyziklani/ACTION_REMOVE_FILTER';

export const removeFilter = (filter: Filter) => {
    return {
        filter,
        type: ACTION_REMOVE_FILTER,
    };
};

export const ACTION_SET_AUTO_SWITCH = '@@fyziklani/ACTION_SET_AUTO_SWITCH';

export const setAutoSwitch = (state: boolean) => {
    return {
        state,
        type: ACTION_SET_AUTO_SWITCH,
    };
};

export const ACTION_SET_FILTER = '@@fyziklani/ACTION_SET_FILTER';

export const setFilter = (filter: Filter) => {
    return {
        filter,
        type: ACTION_SET_FILTER,
    };
};
