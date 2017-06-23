export const SET_NEXT_TABLE_FILTER = 'SET_NEXT_TABLE_FILTER';

export const setNextFilter = () => {
    return {
        type: SET_NEXT_TABLE_FILTER,
    };
};

export const SET_USER_TABLE_CATEGORY = 'SET_USER_TABLE_CATEGORY';

export const setCategory = (category) => {
    return {
        type: SET_USER_TABLE_CATEGORY,
        category,
    };
};

export const SET_USER_TABLE_ROOM = 'SET_USER_TABLE_ROOM';

export const setRoom = (room) => {
    return {
        type: SET_USER_TABLE_ROOM,
        room,
    };
};

export const SET_USER_FILTER = 'CHANGE_FILTER';

export const setUserFilter = (filter) => {
    return {
        type: SET_USER_FILTER,
        filter,
    };
};
