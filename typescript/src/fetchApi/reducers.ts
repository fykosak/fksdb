import { NetteActions } from '@appsCollector/netteActions';
import {
    ACTION_FETCH_FAIL,
    ACTION_FETCH_START,
    ACTION_FETCH_SUCCESS,
    ActionFetchFail,
    ActionFetchStart,
    ActionFetchSuccess,
} from './actions';
import {
    Message,
    Response2,
} from './interfaces';

export interface State<T = any> {
    [accessKey: string]: {
        submitting?: boolean;
        error?: Error | any;
        messages?: Message[];
        actions?: NetteActions;
    };
}

const fetchStart = (state: State, action: ActionFetchStart): State => {
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

export function fetchApi<D = any>(state: State = initState, action: any): State {
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
