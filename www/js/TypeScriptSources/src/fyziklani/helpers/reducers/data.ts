import { ACTION_SUBMIT_SUCCESS } from '../../../fetch-api/actions/submit';
import { IActionSubmitSuccess } from '../../../fetch-api/middleware/interfaces';
import { IResponseData } from '../downloader/actions/';
import {
    IRoom,
    ISubmits,
    ITask,
    ITeam,
} from '../interfaces';

export interface IFyziklaniDataState {
    submits?: ISubmits;
    tasks?: ITask[];
    teams?: ITeam[];
    rooms?: IRoom[];
    categories?: string[];
}

const addData = (state: IFyziklaniDataState, action: IActionSubmitSuccess<IResponseData>): IFyziklaniDataState => {
    const {submits, tasks, teams, rooms, categories} = action.data.responseData;
    return {
        ...state,
        categories,
        rooms,
        submits: {
            ...state.submits,
            ...submits,
        },
        tasks,
        teams,
    };
};

export const fyziklaniData = (state: IFyziklaniDataState = {}, action): IFyziklaniDataState => {
    switch (action.type) {
        case ACTION_SUBMIT_SUCCESS:
            return addData(state, action);
        default:
            return state;
    }
};
