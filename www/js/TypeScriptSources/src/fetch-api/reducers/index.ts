import {
    ACTION_SUBMIT_FAIL,
    ACTION_SUBMIT_START,
    ACTION_SUBMIT_SUCCESS,
} from '../actions/';
import {
    IMessage,
    IResponse,
    ISubmitAction,
    ISubmitFailAction,
    ISubmitSuccessAction,
} from '../middleware/interfaces';

export interface IFetchApiState {
    [accessKey: string]: {
        submitting?: boolean;
        error?: any;
        messages?: IMessage[];
    };
}

const submitStart = (state: IFetchApiState, action: ISubmitAction): IFetchApiState => {
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
const submitFail = (state: IFetchApiState, action: ISubmitFailAction): IFetchApiState => {
    const {accessKey} = action;
    return {
        ...state,
        [accessKey]: {
            ...state[accessKey],
            error: action.error,
            messages: [{text: action.error.toString(), level: 'danger'}],
            submitting: false,
        },
    };
};
const submitSuccess = (state: IFetchApiState, action: ISubmitSuccessAction<any>): IFetchApiState => {
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

const initState: IFetchApiState = {};

export const fetchApi = (state: IFetchApiState = initState, action): IFetchApiState => {
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
