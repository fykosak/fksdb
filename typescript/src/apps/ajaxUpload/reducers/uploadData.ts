import { UploadDataItem } from '@apps/ajaxUpload/middleware/uploadDataItem';
import { ACTION_FETCH_SUCCESS } from '@fetchApi/actions/submit';
import { ActionFetchSuccess,  Response2 } from '@fetchApi/middleware/interfaces';

export interface State {
    submit: UploadDataItem;
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<Response2<UploadDataItem>>): State => {
    return {
        submit: {...action.data.data},
    };
};

export const uploadData = (state: State = null, action): State => {
    switch (action.type) {
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, action);
        default:
            return state;
    }
};
