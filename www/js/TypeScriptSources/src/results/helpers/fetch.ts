import { Dispatch } from 'react-redux';
import { netteFetch } from '../../fetch-api/middleware/fetch';
import { ISubmits } from '../../shared/interfaces';
import {
    fetchFail,
    newDataArrived,
} from '../actions/downloader';

interface IRequestData {
    lastUpdated?: string;
}

interface IResponseData {
    times: any;
    submits: ISubmits;
    isOrg: boolean;
    lastUpdated: string;
    refreshDelay: number;
}

export const fetchResults = (dispatch, oldLastUpdated: string = null): Promise<any> => {
    const data: any = {};
    if (oldLastUpdated) {
        data.lastUpdated = oldLastUpdated;
    }
    return netteFetch<IRequestData, IResponseData>(
        data,
        (arrivedData) => {
            const {times, submits, isOrg, lastUpdated, refreshDelay} = arrivedData.data;
            dispatch(newDataArrived(lastUpdated, refreshDelay, times, submits, isOrg, true));
        },
        (e) => {
            dispatch(fetchFail(e));
        },
    );
};

export const waitForFetch = (dispatch: Dispatch<any>, delay: number, lastUpdated: string = null): any => {
    return setTimeout(() => {
        fetchResults(dispatch, lastUpdated);
    }, delay);
};
