import {
    ACTION_CHANGE_ACCOMMODATION,
    ACTION_SET_INITIAL_DATA,
} from '../actions/index';

export interface IAccommodationState {
    [date: string]: number;
}

const setAccommodation = (state: IAccommodationState, action): IAccommodationState => {
    return {
        ...state,
        [action.date]: action.accommodationId,
    };
};

const setInitialData = (state: IAccommodationState, action): IAccommodationState => {
    if (action.data) {
        return action.data;
    }
    return state;
};

export const accommodation = (state: IAccommodationState = {}, action): IAccommodationState => {
    switch (action.type) {
        case ACTION_CHANGE_ACCOMMODATION:
            return setAccommodation(state, action);
        case ACTION_SET_INITIAL_DATA:
            return setInitialData(state, action);
        default:
            return state;
    }
};
