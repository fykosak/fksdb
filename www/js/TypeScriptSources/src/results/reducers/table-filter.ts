import { IRoom } from '../../shared/interfaces';
import {
    SET_INITIAL_PARAMS,
} from '../actions/downloader';
import {
    SET_AUTO_SWITCH,
    SET_NEXT_TABLE_FILTER,
    SET_USER_FILTER,
    SET_USER_TABLE_CATEGORY,
    SET_USER_TABLE_ROOM,

} from '../actions/table-filter';
import { Filter } from '../helpers/filters/filters';

export interface IState {
    userFilter?: Filter;
    autoSwitch: boolean;
    filterId: number;
    roomId?: number;
    category?: string;
    filters: Filter[];
}

const setNextFilter = (state: IState): IState => {
    const {filterId} = state;
    return {
        ...state,
        filterId: (filterId + 1) % 3,
    };
};

const setRoom = (state: IState, action): IState => {
    const {roomId} = action;
    return {
        ...state,
        roomId,
    };
};

const setFilters = (state: IState, action): IState => {
    const filters: Filter[] = [
        new Filter({roomId: null, category: null, name: 'ALL'}),
        new Filter({roomId: null, category: 'A', name: 'Category A'}),
        new Filter({roomId: null, category: 'B', name: 'Category B'}),
        new Filter({roomId: null, category: 'C', name: 'Category C'}),
        new Filter({roomId: null, category: 'F', name: 'Category F'}),
    ];
    action.rooms.forEach((room: IRoom) => {
        filters.push(new Filter({roomId: room.roomId, category: null, name: 'Room ' + room.name}));
    });

    return {
        ...state,
        filters,
    };
};

const setCategory = (state: IState, action): IState => {
    const {category} = action;
    return {
        ...state,
        category,
    };
};

const setAutoSwitch = (state: IState, action): IState => {
    return {
        ...state,
        autoSwitch: action.state,
    };
};

const setUserFilter = (state: IState, action): IState => {
    return {
        ...state,
        userFilter: action.filter,
    };
};

export const tableFilter = (state: IState = {filterId: 0, autoSwitch: false, filters: []}, action): IState => {

    switch (action.type) {
        case SET_NEXT_TABLE_FILTER:
            return setNextFilter(state);
        case SET_USER_TABLE_CATEGORY:
            return setCategory(state, action);
        case SET_USER_TABLE_ROOM:
            return setRoom(state, action);
        case SET_USER_FILTER:
            return setUserFilter(state, action);
        case SET_INITIAL_PARAMS:
            return setFilters(state, action);
        case SET_AUTO_SWITCH:
            return setAutoSwitch(state, action);
        default:
            return state;
    }
};
