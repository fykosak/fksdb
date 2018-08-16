import { ACTION_SUBMIT_SUCCESS } from '../../../fetch-api/actions/submit';
import { ISubmitSuccessAction } from '../../../fetch-api/middleware/interfaces';
import { IFyziklaniResponse } from '../components/downloader/fetch';
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

const addData = (state: IFyziklaniDataState, action: ISubmitSuccessAction<IFyziklaniResponse>): IFyziklaniDataState => {
    const {submits, tasks, teams, rooms, categories} = action.data.data;
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
