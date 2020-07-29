import jqXHR = JQuery.jqXHR;
import { NetteActions } from '@appsCollector';
import {
    ACTION_FETCH_FAIL,
    ACTION_FETCH_START,
    ACTION_FETCH_SUCCESS,
} from '../actions/submit';
import {
    ActionFetch,
    ActionFetchFail,
    ActionFetchSuccess,
    Message,
    Response2,
} from '../middleware/interfaces';

export interface State<T = any> {
    [accessKey: string]: {
        submitting?: boolean;
        error?: jqXHR<T>;
        messages?: Message[];
        actions?: NetteActions;
    };
}

const fetchStart = (state: State, action: ActionFetch): State => {
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
const fetchFail = (state: State, action: ActionFetchFail): State => {
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

function fetchSuccess<D = any>(state: State, action: ActionFetchSuccess<Response2<D>>): State {
    const {accessKey} = action;
    return {
        ...state,
        [accessKey]: {
            ...state[accessKey],
            actions: action.data.actions,
            messages: action.data.messages,
            submitting: false,
        },
    };
}

const initState: State = {};

export function submit<D = any>(state: State = initState, action: any): State {
    switch (action.type) {
        case ACTION_FETCH_START:
            return fetchStart(state, action);
        case ACTION_FETCH_FAIL:
            return fetchFail(state, action);
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess<D>(state, action);
        default:
            return state;
    }
}
