import { ACTION_SUBMIT_SUCCESS } from '../../../../fetch-api/actions/submit';
import { IActionSubmitSuccess } from '../../../../fetch-api/middleware/interfaces';
import { IResponseData } from '../../downloader/actions/';
import {
    ACTION_SET_HARD_VISIBLE,
    IActionSetHardVisible,
} from '../actions/';

export interface IFyziklaniOptionsState {
    isReady?: boolean;
    hardVisible?: boolean;
    isOrg?: boolean;
}

const setHardVisible = (state: IFyziklaniOptionsState, action: IActionSetHardVisible): IFyziklaniOptionsState => {
    return {
        ...state,
        hardVisible: action.hardVisible,
    };
};

const setStatuses = (state: IFyziklaniOptionsState, action: IActionSubmitSuccess<IResponseData>): IFyziklaniOptionsState => {
    const {isOrg} = action.data.responseData;
    return {
        ...state,
        isOrg,
        isReady: true,
    };
};

export const fyziklaniOptions = (state: IFyziklaniOptionsState = {}, action): IFyziklaniOptionsState => {
    switch (action.type) {
        case ACTION_SET_HARD_VISIBLE:
            return setHardVisible(state, action);
        case ACTION_SUBMIT_SUCCESS:
            return setStatuses(state, action);
        default:
            return state;
    }
};
