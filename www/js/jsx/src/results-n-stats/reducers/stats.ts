import {
    SET_STATS_ACTIVE_POINTS,
    SET_STATS_DE_ACTIVE_POINTS,
    SET_STATS_TEAM_ID,
} from '../actions/stats';
export interface IState {
    teamID?: number;
    activePoints?: number;
}
const setTeamID = (state: IState, action): IState => {
    const { teamID } = action;
    return {
        ...state,
        teamID,
    };
};
const setActivePoints = (state: IState, action): IState => {
    const { activePoints } = action;
    return {
        ...state,
        activePoints,
    };
};
const setDeActivePoints = (state: IState): IState => {
    return {
        ...state,
        activePoints: null,
    };
};

export const stats = (state: IState = {}, action): IState => {
    switch (action.type) {
        case SET_STATS_TEAM_ID:
            return setTeamID(state, action);
        case SET_STATS_DE_ACTIVE_POINTS:
            return setDeActivePoints(state);
        case SET_STATS_ACTIVE_POINTS:
            return setActivePoints(state, action);
        default:
            return state;
    }
};
