import { Dispatch } from 'react-redux';
import { dispatchNetteFetch } from '../../../../fetch-api/middleware/fetch';
import {
    IRequest,
    IResponse,
} from '../../../../fetch-api/middleware/interfaces';
import { IFetchApiState } from '../../../../fetch-api/reducers/submit';
import {
    IRoom,
    ISubmits,
    ITask,
    ITeam,
} from '../../interfaces/';
import { IFyziklaniDataState } from '../../reducers/data';

interface IState {
    data: IFyziklaniDataState;
    fetchApi: IFetchApiState;
}

export const fetchResults = (
    accessKey: string,
    dispatch: Dispatch<IState>,
    oldLastUpdated: string = null,
    url: string,
): Promise<IResponse<IResponseData>> => {
    const data: IRequest<string> = {
        act: '@@fyziklani/results',
        requestData: null,
    };

    if (oldLastUpdated) {
        data.requestData = oldLastUpdated;
    }
    return dispatchNetteFetch<string, IResponseData, IState>(accessKey, dispatch, data, () => null, () => null, url);
};

export const waitForFetch = (
    accessKey: string,
    dispatch: Dispatch<IState>,
    delay: number,
    lastUpdated: string = null,
    url: string,
): any => {
    return setTimeout(() => {
        return fetchResults(accessKey, dispatch, lastUpdated, url);
    }, delay);
};

export interface IResponseData {
    basePath: string;
    gameEnd: string;
    gameStart: string;
    isOrg: boolean;
    lastUpdated: string;
    refreshDelay: number;
    submits: ISubmits;
    times: {
        toStart: number;
        toEnd: number;
        visible: boolean;
    };
    teams: ITeam[];
    tasks: ITask[];
    rooms: IRoom[];
    categories: string[];
}
