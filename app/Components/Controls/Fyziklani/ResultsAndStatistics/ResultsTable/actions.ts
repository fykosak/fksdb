import { Filter } from './filter';
import { Action } from 'redux';

export interface FilterAction extends Action<string> {
    filter: Filter;
}

export const ACTION_REMOVE_FILTER = '@@fyziklani/ACTION_REMOVE_FILTER';
export const removeFilter = (filter: Filter): FilterAction => {
    return {
        filter,
        type: ACTION_REMOVE_FILTER,
    };
};

export const ACTION_SET_FILTER = '@@fyziklani/ACTION_SET_FILTER';

export const setFilter = (filter: Filter): FilterAction => {
    return {
        filter,
        type: ACTION_SET_FILTER,
    };
};
