import {
    ACTION_SET_PARAMS,
    ActionSetParams,
} from '../actions';

export interface State {
    position: number;
    cols: number;
    rows: number;
    delay: number;
    category?: string;
}

const initialState: State = {
    category: null,
    cols: 2,
    delay: 10 * 1000,
    position: 0,
    rows: 20,
};

export const presentation = (state: State = initialState, action: ActionSetParams): State => {

    switch (action.type) {
        case ACTION_SET_PARAMS:
            return {...state, ...action.data}
        default:
            return state;
    }
};
