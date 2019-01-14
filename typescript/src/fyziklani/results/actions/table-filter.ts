import { Action } from 'redux';
import { Filter } from '../middleware/results/filters/filter';

export const ACTION_SET_NEXT_TABLE_FILTER = '@@fyziklani/ACTION_SET_NEXT_TABLE_FILTER';

export const setNextFilter = (): Action<string> => {
    return {
        type: ACTION_SET_NEXT_TABLE_FILTER,
    };
};

export interface ActionWithFilter extends Action<string> {
    filter: Filter;
}

export const ACTION_ADD_FILTER = '@@fyziklani/ACTION_ADD_FILTER';

export const addFilter = (filter: Filter): ActionWithFilter => {
    return {
        filter,
        type: ACTION_ADD_FILTER,
    };
};

export const ACTION_REMOVE_FILTER = '@@fyziklani/ACTION_REMOVE_FILTER';

export const removeFilter = (filter: Filter): ActionWithFilter => {
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

export const setFilter = (filter: Filter): ActionWithFilter => {
    return {
        filter,
        type: ACTION_SET_FILTER,
    };
};
