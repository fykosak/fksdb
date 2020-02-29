import { dispatchNetteFetch } from '@fetchApi/middleware/fetch';
import {
    Request,
    Response,
} from '@fetchApi/middleware/interfaces';
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
    accessKey: string,
    dispatch: Dispatch<Action<string>>,
    oldLastUpdated: string = null,
    url: string,
): Promise<Response<ResponseData>> => {
    const data: Request<string> = {
        act: '@@fyziklani/results',
        requestData: null,
    };
    if (oldLastUpdated) {
        data.requestData = oldLastUpdated;
    }
    return dispatchNetteFetch<string, ResponseData, State>(accessKey, dispatch, data, () => null, () => null, url);
};

export const waitForFetch = (
    accessKey: string,
    dispatch: Dispatch<Action<string>>,
    delay: number,
    lastUpdated: string = null,
    url: string,
): any => {
    return setTimeout(() => {
        return fetchResults(accessKey, dispatch, lastUpdated, url);
    }, delay);
};
