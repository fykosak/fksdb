export const SET_STATS_ACTIVE_POINTS = 'SET_STATS_ACTIVE_POINTS';

export const setActivePoints = (activePoints: number) => {
    return {
        type: SET_STATS_ACTIVE_POINTS,
        activePoints,
    };
};

export const SET_STATS_TEAM_ID = 'SET_STATS_TEAM_ID';

export const setTeamID = (teamID: number) => {
    return {
        type: SET_STATS_TEAM_ID,
        teamID,
    };
};

export const SET_STATS_DE_ACTIVE_POINTS = 'SET_STATS_DE_ACTIVE_POINTS';

export const setDeActivePoints = () => {
    return {
        type: SET_STATS_DE_ACTIVE_POINTS,
    };
};
