import { Filter } from '../helpers/filters/filters';
export const SET_NEXT_TABLE_FILTER = 'SET_NEXT_TABLE_FILTER';

export const setNextFilter = () => {
    return {
        type: SET_NEXT_TABLE_FILTER,
    };
};

export const SET_USER_TABLE_CATEGORY = 'SET_USER_TABLE_CATEGORY';

export const setCategory = (category: string) => {
    return {
        category,
        type: SET_USER_TABLE_CATEGORY,
    };
};

export const SET_USER_TABLE_ROOM = 'SET_USER_TABLE_ROOM';

export const setRoom = (room: string) => {
    return {
        room,
        type: SET_USER_TABLE_ROOM,
    };
};

export const SET_USER_FILTER = 'CHANGE_FILTER';

export const setUserFilter = (filter: Filter) => {
    return {
        filter,
        type: SET_USER_FILTER,
    };
};
