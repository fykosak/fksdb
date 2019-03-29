import { ACTION_SUBMIT_SUCCESS } from '../../../fetch-api/actions/submit';
import { ActionSubmitSuccess } from '../../../fetch-api/middleware/interfaces';
import { ResponseData } from '../downloader/actions/';
import {
    Room,
    Submits,
    Task,
    Team,
} from '../interfaces';

export interface State {
    submits?: Submits;
    tasks?: Task[];
    teams?: Team[];
    rooms?: Room[];
    categories?: string[];
    availablePoints?: number[];
}

const addData = (state: State, action: ActionSubmitSuccess<ResponseData>): State => {
    const {submits, tasks, teams, rooms, categories, availablePoints} = action.data.responseData;
    return {
        ...state,
        availablePoints: availablePoints.map((value) => +value),
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

export const fyziklaniData = (state: State = {}, action): State => {
    switch (action.type) {
        case ACTION_SUBMIT_SUCCESS:
            return addData(state, action);
        default:
            return state;
    }
};
