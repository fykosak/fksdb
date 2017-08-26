import {
    SET_NEXT_TABLE_FILTER,
    SET_USER_TABLE_CATEGORY,
    SET_USER_TABLE_ROOM,
    SET_USER_FILTER,
} from '../actions/table-filter';
import { Filter } from '../helpers/filters/filters';

export interface IState {
    userFilter?: Filter,
    autoSwitch: boolean;
    filterID: number;
    room?: string;
    category?: string;
}

const setNextFilter = (state: IState): IState => {
    const { filterID } = state;
    return {
        ...state,
        filterID: (filterID + 1) % 3,
    };
};

const setRoom = (state: IState, action): IState => {
    const { room } = action;
    return {
        ...state,
        room,
    };
};

const setCategory = (state: IState, action): IState => {
    const { category } = action;
    return {
        ...state,
        category,
    };
};

const setUserFilter = (state: IState, action): IState => {
    return {
        ...state,
        autoSwitch: !action.filter,
        userFilter: action.filter,
    };
};

export const tableFilter = (state: IState = { filterID: 0, autoSwitch: true }, action): IState => {

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
