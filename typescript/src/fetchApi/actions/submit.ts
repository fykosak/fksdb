import {
    ActionFetch,
    ActionFetchFail,
    ActionFetchSuccess,
    Response2,
} from '../middleware/interfaces';
import jqXHR = JQuery.jqXHR;

export const ACTION_FETCH_SUCCESS = 'ACTION_FETCH_SUCCESS';

export function fetchSuccess<Data>(data: Response2<Data>, accessKey: string): ActionFetchSuccess<Response2<Data>> {
    return {
        accessKey,
        data,
        type: ACTION_FETCH_SUCCESS,
    };
}

export const ACTION_FETCH_FAIL = 'ACTION_FETCH_FAIL';

export function fetchFail<T = any>(error: jqXHR<T>, accessKey: string): ActionFetchFail<T> {
    return {
        accessKey,
        error,
        type: ACTION_FETCH_FAIL,
    };
}

export const ACTION_FETCH_START = 'ACTION_FETCH_START';
export const fetchStart = (accessKey: string): ActionFetch => {
    return {
        accessKey,
        type: ACTION_FETCH_START,
    };
};
