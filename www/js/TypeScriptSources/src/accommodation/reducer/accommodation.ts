import { ACTION_CHANGE_ACCOMMODATION } from '../actions';

export interface IState {
    [date: string]: number;
}

const setAccommodation = (state, action): IState => {
    return {
        ...state,
        [action.date]: action.accommodationId,
    };
};

export const accommodation = (state: IState = {}, action): IState => {
    switch (action.type) {
        case ACTION_CHANGE_ACCOMMODATION:
            return setAccommodation(state, action);
        default:
            return state;
    }
};
