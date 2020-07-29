import {
    ACTION_FETCH_SUCCESS,
    ActionFetchSuccess,
} from '@fetchApi/actions/fetch';
import { Response2 } from '@fetchApi/middleware/interfaces';
import { ResponseData } from '../../downloader/interfaces';
import {
    ACTION_SET_HARD_VISIBLE,
    ActionSetHardVisible,
} from '../actions';

export interface State {
    isReady?: boolean;
    hardVisible?: boolean;
    isOrg?: boolean;
}

const setHardVisible = (state: State, action: ActionSetHardVisible): State => {
    return {
        ...state,
        hardVisible: action.hardVisible,
    };
};

const fetchSuccess = (state: State, action: ActionFetchSuccess<Response2<ResponseData>>): State => {
    const {isOrg} = action.data.data;
    return {
        ...state,
        isOrg,
        isReady: true,
    };
};

export const fyziklaniOptions = (state: State = {}, action): State => {
    switch (action.type) {
        case ACTION_SET_HARD_VISIBLE:
            return setHardVisible(state, action);
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, action);
        default:
            return state;
    }
};
