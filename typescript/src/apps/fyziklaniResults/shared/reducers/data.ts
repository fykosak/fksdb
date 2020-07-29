import { ACTION_FETCH_SUCCESS, ActionFetchSuccess } from '@fetchApi/actions/fetch';
import { Response2 } from '@fetchApi/middleware/interfaces';
import {
    Submits,
    Task,
    Team,
} from '../../../fyziklani/helpers/interfaces';
import { ResponseData } from '../../downloader/interfaces';

export interface State {
    submits?: Submits;
    tasks?: Task[];
    teams?: Team[];
    // rooms?: Room[];
    categories?: string[];
    availablePoints?: number[];
    tasksOnBoard?: number;
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<Response2<ResponseData>>): State => {
    const {submits, tasks, teams, categories, availablePoints, tasksOnBoard} = action.data.data;
    return {
        ...state,
        availablePoints: availablePoints.map((value) => +value),
        categories: categories ? categories : state.categories,
        submits: {
            ...state.submits,
            ...submits,
        },
        tasks: tasks ? tasks : state.tasks,
        tasksOnBoard,
        teams: teams ? teams : state.teams,
    };
};

export const fyziklaniData = (state: State = {}, action): State => {
    switch (action.type) {
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, action);
        default:
            return state;
    }
};
