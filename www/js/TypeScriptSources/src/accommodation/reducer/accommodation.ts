import { ACTION_CHANGE_ACCOMMODATION } from '../actions';

export interface IAccommodationState {
    [date: string]: number;
}

const setAccommodation = (state, action): IAccommodationState => {
    return {
        ...state,
        [action.date]: action.accommodationId,
    };
};

export const accommodation = (state: IAccommodationState = {}, action): IAccommodationState => {
    switch (action.type) {
        case ACTION_CHANGE_ACCOMMODATION:
            return setAccommodation(state, action);
        default:
            return state;
    }
};
