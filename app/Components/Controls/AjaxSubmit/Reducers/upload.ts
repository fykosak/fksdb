import {
    ACTION_FETCH_SUCCESS,
    ActionFetchSuccess,
} from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/actions';
import { DataResponse } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import { SubmitModel } from 'FKSDB/Models/ORM/Models/submit-model';

export interface State {
    submit?: SubmitModel;
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<DataResponse<SubmitModel>>): State => {
    return {
        submit: {...action.data.data},
    };
};

export const upload = (state: State = {}, action): State => {
    switch (action.type) {
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, action);
        default:
            return state;
    }
};
