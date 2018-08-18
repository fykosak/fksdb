import { Dispatch } from 'react-redux';
import { dispatchNetteFetch } from '../../../../fetch-api/middleware/fetch';
import {
    IRequest,
} from '../../../../fetch-api/middleware/interfaces';
import {
    IRoom,
    ISubmits,
    ITask,
    ITeam,
} from '../../interfaces';

export const fetchResults = (accessKey: string, dispatch: Dispatch<any>, oldLastUpdated: string = null): Promise<any> => {
    const data: IRequest<string> = {
        act: '@@fyziklani/results',
        data: null,
    };

    if (oldLastUpdated) {
        data.data = oldLastUpdated;
    }
    return dispatchNetteFetch<string, any, any>(accessKey, dispatch, data, () => null, () => null);
};

export const waitForFetch = (accessKey: string, dispatch: Dispatch<any>, delay: number, lastUpdated: string = null): any => {
    return setTimeout(() => {
        return fetchResults(accessKey, dispatch, lastUpdated);
    }, delay);
};

export interface IFyziklaniResponse {
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
