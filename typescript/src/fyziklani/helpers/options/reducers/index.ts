import { ACTION_SUBMIT_SUCCESS } from '../../../../fetch-api/actions/submit';
import { ActionSubmitSuccess } from '../../../../fetch-api/middleware/interfaces';
import { ResponseData } from '../../downloader/actions/';
import {
    ACTION_SET_HARD_VISIBLE,
    ActionSetHardVisible,
} from '../actions/';

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

const setStatuses = (state: State, action: ActionSubmitSuccess<ResponseData>): State => {
    const {isOrg} = action.data.responseData;
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
        case ACTION_SUBMIT_SUCCESS:
            return setStatuses(state, action);
        default:
            return state;
    }
};
