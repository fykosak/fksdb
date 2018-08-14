import { ACTION_SUBMIT_SUCCESS } from '../../fetch-api/actions/';
import { NEW_DATA_ARRIVED } from '../actions/upload-data';

export interface IState {
    submitting: false;
    deadline?: string;
    href?: string;
    name?: string;
    submitId?: number;
    taskId?: number;
}

const newDataArrived = (state: IState, action): IState => {
    return {
        ...state,
        ...action.data,
    };
};
const submitSuccess = (state: IState, action): IState => {
    return {
        ...state,
        ...action.data.data,
    };
};
const defaultState: IState = {submitting: false};

export const uploadData = (state: IState = defaultState, action): IState => {
    switch (action.type) {
        case ACTION_SUBMIT_SUCCESS:
            return submitSuccess(state, action);
        case NEW_DATA_ARRIVED:
            return newDataArrived(state, action);
        default:
            return state;
    }
};
