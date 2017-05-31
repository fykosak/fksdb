import {updateTimes} from '../actions/times';
import {updateDownloaderOptions} from '../actions/downloader';
import {
    addSubmits,
    setTasks,
    setTeams
} from '../actions/results';
import {
    setReadyStatus,
    setOrgStatus,
} from '../actions/options';

export const fetchResults = (dispatch: Function, lastUpdated: string = null) => {
    const promise = new Promise((resolve, reject) => {
        const data: any = {};
        if (lastUpdated) {
            data.lastUpdated = lastUpdated;
        }
        (<any>$).nette.ajax({
            data,
            success: (data) => {
                resolve(data);
            },
            error: (e) => {
                throw e;
            }
        })
    });
    promise.then((data: any) => {
        const {times, submits, isOrg, lastUpdated, refreshDelay, tasks, teams} = data;
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

    })
};

export const waitForFetch = (dispatch: Function, delay: number, lastUpdated: string = null) => {
    return setTimeout(() => {
        fetchResults(dispatch, lastUpdated);
    }, delay);
};
