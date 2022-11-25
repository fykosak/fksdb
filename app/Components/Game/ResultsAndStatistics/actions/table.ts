import { Filter } from '../Table/filter';
import { Action } from 'redux';

export interface FilterAction extends Action<string> {
    filter: Filter | null;
}

export const ACTION_SET_FILTER = '@@game/ACTION_SET_FILTER';

export const setFilter = (filter: Filter): FilterAction => {
    return {
        filter,
        type: ACTION_SET_FILTER,
    };
};
