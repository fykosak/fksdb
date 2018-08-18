import {
    ACTION_SUBMIT_FAIL,
    ACTION_SUBMIT_START,
    ACTION_SUBMIT_SUCCESS,
} from '../actions/submit';
import {
    IMessage,
    IResponse,
    ISubmitAction,
    ISubmitFailAction,
    ISubmitSuccessAction,
} from '../middleware/interfaces';

export interface IState {
    [accessKey: string]: {
        submitting?: boolean;
        error?: any;
        messages?: IMessage[];
    };
}

export interface IFetchApiState extends IState {
};

const submitStart = (state: IState, action: ISubmitAction): IState => {
    const {accessKey} = action;
    return {
        ...state,
        [accessKey]: {
            ...state[accessKey],
            error: null,
            messages: [],
            submitting: true,
        },
    };
};
const submitFail = (state: IState, action: ISubmitFailAction): IState => {
    const {accessKey} = action;
    return {
        ...state,
        [accessKey]: {
            ...state[accessKey],
            error: action.error,
            messages: [action.error.toString(), 'danger'],
            submitting: false,
        },
    };
};
const submitSuccess = (state: IState, action: ISubmitSuccessAction<any>): IState => {
    const data: IResponse<any> = action.data;
    const {accessKey} = action;
    return {
        ...state,
        [accessKey]: {
            ...state[accessKey],
            messages: data.messages,
            submitting: false,
        },
    };
};

const initState: IState = {};

export const submit = (state: IState = initState, action): IState => {
    switch (action.type) {
        case ACTION_SUBMIT_START:
            return submitStart(state, action);
        case ACTION_SUBMIT_FAIL:
            return submitFail(state, action);
        case ACTION_SUBMIT_SUCCESS:
            return submitSuccess(state, action);
        default:
            return state;
    }
};
