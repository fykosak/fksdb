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
import {
    Room,
    Submits,
    Task,
    Team,
} from '../../interfaces/';
import { State as DataState } from '../../reducers/data';

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
    dispatch: Dispatch<Action>,
    delay: number,
    lastUpdated: string = null,
    url: string,
): any => {
    return setTimeout(() => {
        return fetchResults(accessKey, dispatch, lastUpdated, url);
    }, delay);
};

export interface ResponseData {
    basePath: string;
    availablePoints: number[];
    gameEnd: string;
    gameStart: string;
    isOrg: boolean;
    lastUpdated: string;
    refreshDelay: number;
    submits: Submits;
    times: {
        toStart: number;
        toEnd: number;
        visible: boolean;
    };
    teams: Team[];
    tasks: Task[];
    rooms: Room[];
    categories: string[];
}
