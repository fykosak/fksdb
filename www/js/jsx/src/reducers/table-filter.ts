import {
    SET_NEXT_TABLE_FILTER,
    SET_USER_TABLE_CATEGORY,
    SET_USER_TABLE_ROOM,
    SET_USER_TABLE_AUTO_SWITCH,
} from '../actions/table-filter';
const setNextFilter = (state) => {
    let {filterID} = state;
    return {
        ...state,
        filterID: (filterID + 1) % 3,
    };
};

const setRoom = (state, action) => {
    const {room}= action;
    return {
        ...state,
        room,
    };
};

const setCategory = (state, action) => {
    const {category}= action;
    return {
        ...state,
        category,
    };
};

const setAutoSwitch = (state, action) => {
    const {autoSwitch}= action;
    return {
        ...state,
        autoSwitch,
    };
};

export const tableFilter = (state = {filterID: 0, autoSwitch: false}, action) => {

    switch (action.type) {
        case SET_NEXT_TABLE_FILTER:
            return setNextFilter(state);
        case SET_USER_TABLE_CATEGORY:
            return setCategory(state, action);
        case SET_USER_TABLE_ROOM:
            return setRoom(state, action);
        case SET_USER_TABLE_AUTO_SWITCH:
            return setAutoSwitch(state, action);
        default:
            return state;
    }
};
