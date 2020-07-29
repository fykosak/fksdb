import { Response2 } from '@fetchApi/middleware/interfaces';
import { dispatchFetch } from '@fetchApi/middleware/netteFetch';
import { State as FetchApiState } from '@fetchApi/reducers/submit';
import {
    Action,
    Dispatch,
} from 'redux';
import { State as DataState } from '../../shared/reducers/data';
import { ResponseData } from '../interfaces';

interface State {
    data: DataState;
    fetchApi: FetchApiState;
}

export const fetchResults = (
    url: string,
    accessKey: string,
    dispatch: Dispatch<Action<string>>,
): Promise<Response2<ResponseData>> => {
    return dispatchFetch<ResponseData, State>(url, accessKey, dispatch, JSON.stringify({}));
};

export const waitForFetch = (
    accessKey: string,
    dispatch: Dispatch<Action<string>>,
    delay: number,
    url: string,
): any => {
    return setTimeout(() => {
        return fetchResults(url, accessKey, dispatch);
    }, delay);
};
