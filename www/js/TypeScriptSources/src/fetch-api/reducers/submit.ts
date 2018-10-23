import {
    ACTION_SUBMIT_FAIL,
    ACTION_SUBMIT_START,
    ACTION_SUBMIT_SUCCESS,
} from '../actions/submit';
import {
    IActionSubmit,
    IActionSubmitFail,
    IActionSubmitSuccess,
    IMessage,
    IResponse,
} from '../middleware/interfaces';
import jqXHR = JQuery.jqXHR;

export interface IFetchApiState<T= any> {
    [accessKey: string]: {
        submitting?: boolean;
        error?: jqXHR<T>;
        messages?: IMessage[];
    };
}

const submitStart = (state: IFetchApiState, action: IActionSubmit): IFetchApiState => {
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
const submitFail = (state: IFetchApiState, action: IActionSubmitFail): IFetchApiState => {
    const {accessKey} = action;
    return {
        ...state,
        [accessKey]: {
            ...state[accessKey],
            error: action.error,
            messages: [{
                level: 'danger',
                text: action.error.toString(),
            }],
            submitting: false,
        },
    };
};

function submitSuccess<D= any>(state: IFetchApiState, action: IActionSubmitSuccess<D>): IFetchApiState {
    const data: IResponse<D> = action.data;
    const {accessKey} = action;
    return {
        ...state,
        [accessKey]: {
            ...state[accessKey],
            messages: data.messages,
            submitting: false,
        },
    };
}

const initState: IFetchApiState = {};

export function submit<D= any>(state: IFetchApiState = initState, action): IFetchApiState {
    switch (action.type) {
        case ACTION_SUBMIT_START:
            return submitStart(state, action);
        case ACTION_SUBMIT_FAIL:
            return submitFail(state, action);
        case ACTION_SUBMIT_SUCCESS:
            return submitSuccess<D>(state, action);
        default:
            return state;
    }
}
