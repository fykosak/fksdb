import { UploadDataItem } from '@apps/ajaxUpload/middleware/uploadDataItem';
import { ACTION_SUBMIT_SUCCESS } from '@fetchApi/actions/submit';
import { ActionSubmitSuccess } from '@fetchApi/middleware/interfaces';

export type State = UploadDataItem;

const submitSuccess = (state: State, action: ActionSubmitSuccess<any>): State => {
    return {
        ...state,
        ...action.data.responseData[state.taskId],
    };
};

export const uploadData = (state: State = null, action): State => {
    switch (action.type) {
        case ACTION_SUBMIT_SUCCESS:
            return submitSuccess(state, action);
        default:
            return state;
    }
};
