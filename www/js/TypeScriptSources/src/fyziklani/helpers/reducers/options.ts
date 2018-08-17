import { ACTION_SUBMIT_SUCCESS } from '../../../fetch-api/actions/submit';
import { ISubmitSuccessAction } from '../../../fetch-api/middleware/interfaces';
import { SET_HARD_VISIBLE } from '../../../results/actions/options';
import { IFyziklaniResponse } from '../components/downloader/fetch';

export interface IFyziklaniOptionsState {
    isReady?: boolean;
    hardVisible?: boolean;
    isOrg?: boolean;
}

const setHardVisible = (state: IFyziklaniOptionsState, action): IFyziklaniOptionsState => {
    return {
        ...state,
        hardVisible: action.status,
    };
};

const setStatuses = (state: IFyziklaniOptionsState, action: ISubmitSuccessAction<IFyziklaniResponse>): IFyziklaniOptionsState => {
    const {isOrg} = action.data.data;
    return {
        ...state,
        isOrg,
        isReady: true,
    };
};

export const fyziklaniOptions = (state: IFyziklaniOptionsState = {}, action): IFyziklaniOptionsState => {
    switch (action.type) {
        case SET_HARD_VISIBLE:
            return setHardVisible(state, action);
        case ACTION_SUBMIT_SUCCESS:
            return setStatuses(state, action);
        default:
            return state;
    }
};
