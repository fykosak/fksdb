import {
    IRoom,
    ISubmits,
    ITask,
    ITeam,
} from '../../shared/interfaces';
import { IParams } from '../components/app';

export const NEW_DATA_ARRIVED = 'NEW_DATA_ARRIVED';

interface ITimes {
    toStart: number;
    toEnd: number;
}

export const newDataArrived = (lastUpdated: string,
                               refreshDelay: number,
                               times: ITimes,
                               submits: ISubmits,
                               orgStatus: boolean,
                               readyStatus: boolean) => {
    const {toStart, toEnd} = times;
    return {
        lastUpdated,
        orgStatus,
        readyStatus,
        refreshDelay,
        submits,
        times: {
            ...times,
            toEnd: toEnd * 1000,
            toStart: toStart * 1000,
        },
        type: NEW_DATA_ARRIVED,
    };
};
export const FETCH_FAIL = 'FETCH_FAIL';

export const fetchFail = (e: ExceptionInformation) => {
    return {
        error: e,
        type: FETCH_FAIL,
    };
};

export const SET_INITIAL_PARAMS = 'SET_INITIAL_PARAMS';

export const setInitialParameters = (rooms: IRoom[], tasks: ITask[], teams: ITeam[], params: IParams) => {
    return {
        rooms,
        tasks,
        teams,
        ...params,
        type: SET_INITIAL_PARAMS,
    };
};
