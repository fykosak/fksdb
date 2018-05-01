import {
    NEW_DATA_ARRIVED,
} from '../actions/upload-data';

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
const defaultState: IState = {submitting: false};

export const uploadData = (state: IState = defaultState, action): IState => {
    switch (action.type) {
        case NEW_DATA_ARRIVED:
            return newDataArrived(state, action);
        default:
            return state;
    }
};
