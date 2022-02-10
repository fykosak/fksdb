import { ResponseData } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import { Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniSubmit';
import {
    ACTION_FETCH_SUCCESS,
    ActionFetchSuccess,
} from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/actions';
import { DataResponse } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import { ModelFyziklaniTask } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTask';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';

export interface State {
    submits: Submits;
    tasks: ModelFyziklaniTask[];
    teams: ModelFyziklaniTeam[];
    // rooms?: Room[];
    categories?: string[];
    availablePoints?: number[];
    tasksOnBoard?: number;
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<DataResponse<ResponseData>>): State => {
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

export const fyziklaniData = (state: State = {tasks: [], teams: [], submits: {}}, action): State => {
    switch (action.type) {
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, action);
        default:
            return state;
    }
};
