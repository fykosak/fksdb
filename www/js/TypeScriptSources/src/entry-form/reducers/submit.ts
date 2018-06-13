import {
    ACTION_SUBMIT_FAIL,
    ACTION_SUBMIT_START,
    ACTION_SUBMIT_SUCCESS,
} from '../actions/';

export interface IState {
    submitting?: boolean;
    error?: any;
    msg?: string[];
}

const submitStart = (state: IState): IState => {
    return {
        ...state,
        error: null,
        msg: null,
        submitting: true,
    };
};
const submitFail = (state: IState, action): IState => {
    return {
        ...state,
        error: action.error,
        submitting: false,
    };
};
const submitSuccess = (state: IState, action): IState => {
    return {
        ...state,
        msg: action.data,
        submitting: false,
    };
};

const initState: IState = {};

export const submit = (state: IState = initState, action): IState => {
    switch (action.type) {
        case ACTION_SUBMIT_START:
            return submitStart(state);
        case ACTION_SUBMIT_FAIL:
            return submitFail(state, action);
        case ACTION_SUBMIT_SUCCESS:
            return submitSuccess(state, action);
        default:
            return state;
    }
};
