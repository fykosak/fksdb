import { ACTION_SET_PARAMS } from '../actions/presentation';
import { ACTION_FETCH_SUCCESS } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/actions';

export interface State {
    position: number;
    cols: number;
    rows: number;
    delay: number;
    category?: string;
    hardVisible: boolean;
    isOrganizer: boolean;
}

const initialState: State = {
    category: null,
    cols: 2,
    delay: 10 * 1000,
    position: 0,
    rows: 20,
    hardVisible: false,
    isOrganizer: false,
};

export const presentation = (state: State = initialState, action): State => {

    switch (action.type) {
        case ACTION_SET_PARAMS:
            return {...state, ...action.data};
        case ACTION_FETCH_SUCCESS:
            return {
                ...state,
                isOrganizer: action.data.data.isOrganizer,
            };
        default:
            return state;
    }
};
