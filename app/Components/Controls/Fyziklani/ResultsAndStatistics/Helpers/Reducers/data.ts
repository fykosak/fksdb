import { ResponseData } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import { Submits } from 'FKSDB/Models/FrontEnd/apps/fyziklani/helpers/interfaces';
import { ACTION_FETCH_SUCCESS, ActionFetchSuccess } from 'FKSDB/Models/FrontEnd/Fetch/actions';
import { Response2 } from 'FKSDB/Models/FrontEnd/Fetch/interfaces';
import { ModelFyziklaniTask } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTask';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';

export interface State {
    submits?: Submits;
    tasks?: ModelFyziklaniTask[];
    teams?: ModelFyziklaniTeam[];
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
