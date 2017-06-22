import {
    ADD_SUBMITS,
    SET_TASKS,
    SET_TEAMS
} from '../actions/results';
const addSubmits = (state, action) => {
    const {submits} = action;
    return {
        ...state,
        submits: {
            ...state.submits,
            ...submits,
        },
    };
};

const setTeams = (state, action) => {
    const {teams} = action;
    return {
        ...state,
        teams,
    };
};

const setTasks = (state, action) => {
    const {tasks} = action;
    return {
        ...state,
        tasks,
    };
};

export const results = (state = {}, action) => {
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
