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

export interface FetchApiState<T = any> {
    [accessKey: string]: {
        submitting?: boolean;
        error?: Error | any;
        messages?: Message[];
        actions?: NetteActions;
    };
}

const fetchStart = (state: FetchApiState, action: ActionFetchStart): FetchApiState => {
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
const fetchFail = (state: FetchApiState, action: ActionFetchFail): FetchApiState => {
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

function fetchSuccess<D = any>(state: FetchApiState, action: ActionFetchSuccess<Response2<D>>): FetchApiState {
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

const initState: FetchApiState = {};

export function fetchApi<D = any>(state: FetchApiState = initState, action: any): FetchApiState {
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
