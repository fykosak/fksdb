import {SET_NEXT_TABLE_FILTER} from '../actions/table-filter';
const setNextFilter = (state) => {
    let {filterID} = state;
    return {
        ...state,
        filterID: (filterID + 1) % 3,
    }
};

export const tableFilter = (state = {}, action) => {

    switch (action.type) {
        case SET_NEXT_TABLE_FILTER:
            return setNextFilter(state);
        default:
            return state;
    }
};