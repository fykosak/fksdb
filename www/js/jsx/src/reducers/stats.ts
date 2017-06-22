import {
    SET_STATS_TEAM_ID,
    SET_STATS_ACTIVE_POINTS,
} from '../actions/stats';
const setTeamID = (state, action) => {
    let {teamID} = action;
    return {
        ...state,
        teamID,
    };
};
const setActivePoints = (state, action) => {
    let {activePoints} = action;
    return {
        ...state,
        activePoints,
    };
};

export const stats = (state = {}, action) => {
    switch (action.type) {
        case SET_STATS_TEAM_ID:
            return setTeamID(state, action);
        case SET_STATS_ACTIVE_POINTS:
            return setActivePoints(state, action);
        default:
            return state;
    }
};
