import {
    SET_NEXT_TABLE_FILTER,
    SET_USER_TABLE_CATEGORY,
    SET_USER_TABLE_ROOM,
    SET_USER_FILTER,
} from '../actions/table-filter';

const setNextFilter = (state) => {
    let {filterID} = state;
    return {
        ...state,
        filterID: (filterID + 1) % 3,
    };
};

const setRoom = (state, action) => {
    const {room} = action;
    return {
        ...state,
        room,
    };
};

const setCategory = (state, action) => {
    const {category} = action;
    return {
        ...state,
        category,
    };
};


const setUserFilter = (state, action) => {
    return {
        ...state,
        userFilter: action.filter,
        autoSwitch: !action.filter,
    };
};

export const tableFilter = (state = {filterID: 0, autoSwitch: true}, action) => {

    switch (action.type) {
        case SET_NEXT_TABLE_FILTER:
            return setNextFilter(state);
        case SET_USER_TABLE_CATEGORY:
            return setCategory(state, action);
        case SET_USER_TABLE_ROOM:
            return setRoom(state, action);
        case SET_USER_FILTER:
            return setUserFilter(state, action);
        default:
            return state;
    }
};
