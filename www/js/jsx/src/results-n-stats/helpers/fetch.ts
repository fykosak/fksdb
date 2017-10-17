import { updateTimes } from '../actions/times';
import { updateDownloaderOptions } from '../actions/downloader';
import {
    addSubmits,
    setTasks,
    setTeams,
} from '../actions/results';
import {
    setOrgStatus,
    setReadyStatus,
} from '../actions/options';
import { Dispatch } from 'react-redux';

export const fetchResults = (dispatch, oldLastUpdated: string = null) => {
    const promise = new Promise((resolve) => {
        const data: any = {};
        if (oldLastUpdated) {
            data.lastUpdated = oldLastUpdated;
        }
        const netteJQuery: any = $;
        netteJQuery.nette.ajax({
            data,
            error: (e) => {
                throw e;
            },
            success: (d) => {
                resolve(d);
            },
        });
    });
    promise.then((data: any) => {
        const { times, submits, isOrg, lastUpdated, refreshDelay, tasks, teams } = data;
        dispatch(updateTimes(times));
        dispatch(updateDownloaderOptions(lastUpdated, refreshDelay));
        dispatch(addSubmits(submits));

        if (tasks) {
            dispatch(setTasks(tasks));
        }
        if (teams) {
            dispatch(setTeams(teams));
        }

        dispatch(setOrgStatus(isOrg));
        dispatch(setReadyStatus(true));
    });
};

export const waitForFetch = (dispatch: Dispatch<any>, delay: number, lastUpdated: string = null): any => {
    return setTimeout(() => {
        fetchResults(dispatch, lastUpdated);
    }, delay);
};
