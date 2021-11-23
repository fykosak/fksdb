import { ACTION_FETCH_SUCCESS, ActionFetchSuccess } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/actions';
import { Response } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import { ModelSubmit } from 'FKSDB/Models/ORM/Models/modelSubmit';

export interface State {
    submit?: ModelSubmit;
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<Response<ModelSubmit>>): State => {
    return {
        submit: {...action.data.data},
    };
};

export const uploadData = (state: State = {}, action): State => {
    switch (action.type) {
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, action);
        default:
            return state;
    }
};
