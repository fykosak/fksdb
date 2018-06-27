import {
    NEW_DATA_ARRIVED,
    SET_INITIAL_PARAMS,
} from '../actions/downloader';

import {
    ISubmits,
    ITask,
    ITeam,
} from '../../shared/interfaces';

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
const setInitialParams = (state: IState, action): IState => {
    const {tasks, teams} = action;
    return {
        tasks,
        teams,
        ...state,
    };
};

export const results = (state: IState = {}, action): IState => {
    switch (action.type) {
        case NEW_DATA_ARRIVED:
            return addSubmits(state, action);
        case SET_INITIAL_PARAMS:
            return setInitialParams(state, action);
        default:
            return state;
    }
};
