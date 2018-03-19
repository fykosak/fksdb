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

export const setRoom = (roomId: number) => {
    return {
        roomId,
        type: SET_USER_TABLE_ROOM,
    };
};

export const SET_USER_FILTER = 'CHANGE_FILTER';

export const setUserFilter = (filter: Filter, page: string) => {
    return {
        filter,
        page,
        type: SET_USER_FILTER,
    };
};

export const SET_AUTO_SWITCH = 'SET_AUTO_SWITCH';

export const setAutoSwitch = (state: boolean) => {
    return {
        state,
        type: SET_AUTO_SWITCH,
    };
};
