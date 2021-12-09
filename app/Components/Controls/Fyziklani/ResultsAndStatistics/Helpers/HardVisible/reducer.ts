import { ResponseData } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import {
    ACTION_FETCH_SUCCESS,
    ActionFetchSuccess,
} from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/actions';
import { DataResponse } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import {
    ACTION_SET_HARD_VISIBLE,
    ActionSetHardVisible,
} from './actions';
import { Action } from 'redux';

export interface State {
    hardVisible?: boolean;
    isOrg?: boolean;
}

const setHardVisible = (state: State, action: ActionSetHardVisible): State => {
    return {
        ...state,
        hardVisible: action.hardVisible,
    };
};

const fetchSuccess = (state: State, action: ActionFetchSuccess<DataResponse<ResponseData>>): State => {
    const {isOrg} = action.data.data;
    return {
        ...state,
        isOrg,
    };
};

export const fyziklaniOptions = (state: State = {}, action: Action<string>): State => {
    switch (action.type) {
        case ACTION_SET_HARD_VISIBLE:
            // @ts-ignore
            return setHardVisible(state, action);
        case ACTION_FETCH_SUCCESS:
            // @ts-ignore
            return fetchSuccess(state, action);
        default:
            return state;
    }
};
