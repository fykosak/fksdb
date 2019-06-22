import {
    Message,
    Response,
} from '../../fetch-api/middleware/interfaces';
import {
    ACTION_SUBMIT_FAIL,
    ACTION_SUBMIT_START,
    ACTION_SUBMIT_SUCCESS,
} from '../../fetch-api/actions/submit';

export interface IState {
    submitting?: boolean;
    error?: any;
    messages?: Message[];
}

const submitStart = (state: IState): IState => {
    return {
        ...state,
        error: null,
        messages: [],
        submitting: true,
    };
};
const submitFail = (state: IState, action): IState => {
    return {
        ...state,
        error: action.error,
        messages: [action.error.toString(), 'danger'],
        submitting: false,
    };
};
const submitSuccess = (state: IState, action): IState => {
    const data: Response<any> = action.data;
    return {
        ...state,
        messages: data.messages,
        submitting: false,
    };
};

const initState: IState = {
    messages: [],
};

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
