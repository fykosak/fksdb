export const ADD_SUBMITS = 'ADD_SUBMITS';
export const SET_TEAMS = 'SET_TEAMS';
export const SET_TASKS = 'SET_TASKS';

export const addSubmits = (submits) => {
    return {
        type: ADD_SUBMITS,
        submits,
    };
};

export const setTeams = (teams: Array<any>) => {
    return {
        type: SET_TEAMS,
        teams,
    };
};

export const setTasks = (tasks: Array<any>) => {
    return {
        type: SET_TASKS,
        tasks,
    };
};
