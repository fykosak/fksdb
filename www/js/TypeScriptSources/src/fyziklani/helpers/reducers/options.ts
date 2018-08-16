import { ACTION_SUBMIT_SUCCESS } from '../../../fetch-api/actions/submit';
import { ISubmitSuccessAction } from '../../../fetch-api/middleware/interfaces';
import {
    CHANGE_PAGE,
    SET_HARD_VISIBLE,
} from '../../../results/actions/options';
import { IFyziklaniResponse } from '../components/downloader/fetch';

export interface IFyziklaniOptionsState {
    isReady?: boolean;
    hardVisible?: boolean;
    isOrg?: boolean;
    page?: string;
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

const changePage = (state: IFyziklaniOptionsState, action): IFyziklaniOptionsState => {
    const {page} = action;
    return {
        ...state,
        page,
    };
};

export const fyziklaniOptions = (state: IFyziklaniOptionsState = {page: null}, action): IFyziklaniOptionsState => {
    switch (action.type) {
        case SET_HARD_VISIBLE:
            return setHardVisible(state, action);
        case ACTION_SUBMIT_SUCCESS:
            return setStatuses(state, action);
        case CHANGE_PAGE:
            return changePage(state, action);
        default:
            return state;
    }
};
