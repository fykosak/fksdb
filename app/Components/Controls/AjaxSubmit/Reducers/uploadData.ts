import { ACTION_FETCH_SUCCESS, ActionFetchSuccess } from '@FKSDB/Model/FrontEnd/Fetch/actions';
import { Response2 } from '@FKSDB/Model/FrontEnd/Fetch/interfaces';
import { ModelSubmit } from '@FKSDB/Model/ORM/Models/modelSubmit';

export interface State {
    submit?: ModelSubmit;
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<Response2<ModelSubmit>>): State => {
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
