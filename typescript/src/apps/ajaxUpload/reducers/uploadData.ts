import { ACTION_SUBMIT_SUCCESS } from '@fetchApi/actions/submit';
import { ActionSubmitSuccess } from '@fetchApi/middleware/interfaces';

export interface State {
    deadline: string | null;
    href: string;
    name: string;
    submitId: number | null;
    taskId: number;
}

const submitSuccess = (state: State, action: ActionSubmitSuccess<any>): State => {
    return {
        ...state,
        ...action.data.responseData,
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
