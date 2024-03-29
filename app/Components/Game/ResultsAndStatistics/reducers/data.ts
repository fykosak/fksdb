import { ResponseData } from '../../ResultsAndStatistics/Helpers/Downloader/downloader';
import { Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/submit-model';
import {
    ACTION_FETCH_SUCCESS,
    ActionFetchSuccess,
} from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/actions';
import { DataResponse } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';
import { Action } from 'redux';

export interface State {
    submits: Submits;
    tasks: TaskModel[];
    teams: TeamModel[];
    // rooms?: Room[];
    categories?: string[];
    availablePoints?: Array<5 | 3 | 2 | 1>;
    tasksOnBoard?: number;
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<DataResponse<ResponseData>>): State => {
    const {submits, tasks, teams, categories, availablePoints, tasksOnBoard} = action.data.data;
    return {
        ...state,
        availablePoints: (availablePoints.map((value) => +value) as (5 | 3 | 2 | 1)[]),
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

export const data = (state: State = {tasks: [], teams: [], submits: {}}, action: Action<string>): State => {
    switch (action.type) {
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, action as ActionFetchSuccess<DataResponse<ResponseData>>);
        default:
            return state;
    }
};
