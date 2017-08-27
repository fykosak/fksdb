import {
    ADD_SUBMITS,
    SET_TASKS,
    SET_TEAMS,
} from '../actions/results';

import {
    ISubmits,
    ITask,
    ITeam,
} from '../helpers/interfaces';

export interface IState {
    submits?: ISubmits;
    tasks?: ITask[];
    teams?: ITeam[];
}
const addSubmits = (state: IState, action): IState => {
    const {submits} = action;
    return {
        ...state,
        submits: {
            ...state.submits,
            ...submits,
        },
    };
};

const setTeams = (state: IState, action): IState => {
    const {teams} = action;
    return {
        ...state,
        teams,
    };
};

const setTasks = (state: IState, action): IState => {
    const {tasks} = action;
    return {
        ...state,
        tasks,
    };
};

export const results = (state: IState = {}, action): IState => {
    switch (action.type) {
        case ADD_SUBMITS:
            return addSubmits(state, action);
        case SET_TASKS:
            return setTasks(state, action);
        case SET_TEAMS:
            return setTeams(state, action);
        default:
            return state;
    }
};
