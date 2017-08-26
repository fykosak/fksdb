import {
    ISubmits,
    ITask,
    ITeam,
} from '../helpers/interfaces';
export const ADD_SUBMITS = 'ADD_SUBMITS';
export const SET_TEAMS = 'SET_TEAMS';
export const SET_TASKS = 'SET_TASKS';

export const addSubmits = (submits: ISubmits) => {
    return {
        submits,
        type: ADD_SUBMITS,
    };
};

export const setTeams = (teams: ITeam[]) => {
    return {
        teams,
        type: SET_TEAMS,
    };
};

export const setTasks = (tasks: ITask[]) => {
    return {
        tasks,
        type: SET_TASKS,
    };
};
