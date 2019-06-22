import { ACTION_SUBMIT_SUCCESS } from '../../fetch-api/actions/submit';
import { NEW_DATA_ARRIVED } from '../actions/uploadData';
import { ActionSubmitSuccess } from '../../fetch-api/middleware/interfaces';

export interface State {
    submitting: false;
    deadline?: string;
    href?: string;
    name?: string;
    submitId?: number;
    taskId?: number;
}

const newDataArrived = (state: State, action): State => {
    return {
        ...state,
        ...action.data,
    };
};
const submitSuccess = (state: State, action: ActionSubmitSuccess<any>): State => {
    return {
        ...state,
        ...action.data.responseData,
    };
};
const defaultState: State = {submitting: false};

export const uploadData = (state: State = defaultState, action): State => {
    switch (action.type) {
        case ACTION_SUBMIT_SUCCESS:
            return submitSuccess(state, action);
        case NEW_DATA_ARRIVED:
            return newDataArrived(state, action);
        default:
            return state;
    }
};
