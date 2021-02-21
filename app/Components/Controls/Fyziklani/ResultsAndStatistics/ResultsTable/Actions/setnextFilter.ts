import { Action } from 'redux';

export const ACTION_SET_NEXT_TABLE_FILTER = '@@fyziklani/ACTION_SET_NEXT_TABLE_FILTER';

export const setNextFilter = (): Action<string> => {
    return {
        type: ACTION_SET_NEXT_TABLE_FILTER,
    };
};
