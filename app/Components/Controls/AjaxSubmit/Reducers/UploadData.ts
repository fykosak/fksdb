import { ACTION_FETCH_SUCCESS, ActionFetchSuccess } from '@fetchApi/actions';
import { Response2 } from '@fetchApi/interfaces';
import { Submit } from '@FKSDB/Components/Controls/AjaxSubmit/Middleware';

export interface State {
    submit?: Submit;
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<Response2<Submit>>): State => {
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
