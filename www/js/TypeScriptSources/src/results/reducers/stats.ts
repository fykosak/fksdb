import {
    SET_STATS_ACTIVE_POINTS,
    SET_STATS_DE_ACTIVE_POINTS,
    SET_STATS_TEAM_ID,
} from '../actions/stats';

export interface IState {
    teamId?: number;
    activePoints?: number;
}
const setTeamId = (state: IState, action): IState => {
    const { teamId } = action;
    return {
        ...state,
        teamId,
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
            return setTeamId(state, action);
        case SET_STATS_DE_ACTIVE_POINTS:
            return setDeActivePoints(state);
        case SET_STATS_ACTIVE_POINTS:
            return setActivePoints(state, action);
        default:
            return state;
    }
};
