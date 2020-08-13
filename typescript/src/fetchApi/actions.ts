import { Action } from 'redux';
import { Response2 } from './interfaces';

export interface ActionFetchSuccess<D> extends Action<string> {
    data: D;
    accessKey: string;
}

export const ACTION_FETCH_SUCCESS = '@@fetch-api/ACTION_FETCH_SUCCESS';

export function fetchSuccess<Data>(data: Response2<Data>, accessKey: string): ActionFetchSuccess<Response2<Data>> {
    return {
        accessKey,
        data,
        type: ACTION_FETCH_SUCCESS,
    };
}

export interface ActionFetchFail extends Action<string> {
    error: Error | any;
    accessKey: string;
}

export const ACTION_FETCH_FAIL = '@@fetch-api/ACTION_FETCH_FAIL';

export const fetchFail = (error: Error | any, accessKey: string): ActionFetchFail => {
    return {
        accessKey,
        error,
        type: ACTION_FETCH_FAIL,
    };
};

export interface ActionFetchStart extends Action<string> {
    accessKey: string;
}

export const ACTION_FETCH_START = '@@fetch-api/ACTION_FETCH_START';
export const fetchStart = (accessKey: string): ActionFetchStart => {
    return {
        accessKey,
        type: ACTION_FETCH_START,
    };
};
