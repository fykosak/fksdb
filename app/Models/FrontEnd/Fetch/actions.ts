import { Action } from 'redux';
import { Response2 } from './interfaces';

export interface ActionFetchSuccess<D> extends Action<string> {
    data: D;
}

export const ACTION_FETCH_SUCCESS = '@@fetch-api/ACTION_FETCH_SUCCESS';

export function fetchSuccess<Data>(data: Response2<Data>): ActionFetchSuccess<Response2<Data>> {
    return {
        data,
        type: ACTION_FETCH_SUCCESS,
    };
}

export interface ActionFetchFail extends Action<string> {
    error: Error | any;
}

export const ACTION_FETCH_FAIL = '@@fetch-api/ACTION_FETCH_FAIL';

export const fetchFail = (error: Error | any): ActionFetchFail => {
    return {
        error,
        type: ACTION_FETCH_FAIL,
    };
};

export const ACTION_FETCH_START = '@@fetch-api/ACTION_FETCH_START';
export const fetchStart = (): Action<string> => {
    return {
        type: ACTION_FETCH_START,
    };
};
