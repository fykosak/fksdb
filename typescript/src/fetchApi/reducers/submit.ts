import {
    ACTION_SUBMIT_FAIL,
    ACTION_SUBMIT_START,
    ACTION_SUBMIT_SUCCESS,
} from '../actions/submit';
import {
    ActionSubmit,
    ActionSubmitFail,
    ActionSubmitSuccess,
    Message,
    Response,
} from '../middleware/interfaces';
import jqXHR = JQuery.jqXHR;

export interface State<T = any> {
    [accessKey: string]: {
        submitting?: boolean;
        error?: jqXHR<T>;
        messages?: Message[];
    };
}

const submitStart = (state: State, action: ActionSubmit): State => {
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
const submitFail = (state: State, action: ActionSubmitFail): State => {
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

function submitSuccess<D = any>(state: State, action: ActionSubmitSuccess<D>): State {
    const data: Response<D> = action.data;
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

const initState: State = {};

export function submit<D = any>(state: State = initState, action: any): State {
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
