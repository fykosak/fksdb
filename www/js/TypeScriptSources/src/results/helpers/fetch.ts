import { Dispatch } from 'react-redux';
import { netteFetch } from '../../fetch-api/middleware/fetch';
import {
    fetchFail,
    newDataArrived,
} from '../actions/downloader';

export const fetchResults = (dispatch, oldLastUpdated: string = null): Promise<any> => {
    const data: any = {};
    if (oldLastUpdated) {
        data.lastUpdated = oldLastUpdated;
    }
    return netteFetch(
        data,
        (arrivedData: any) => {
            const {times, submits, isOrg, lastUpdated, refreshDelay} = arrivedData;
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
