export const SET_STATS_ACTIVE_POINTS = 'SET_STATS_ACTIVE_POINTS';

export const setActivePoints = (activePoints: number) => {
    return {
        activePoints,
        type: SET_STATS_ACTIVE_POINTS,
    };
};

export const SET_STATS_TEAM_ID = 'SET_STATS_TEAM_ID';

export const setTeamId = (teamId: number) => {
    return {
        teamId,
        type: SET_STATS_TEAM_ID,
    };
};

export const SET_STATS_DE_ACTIVE_POINTS = 'SET_STATS_DE_ACTIVE_POINTS';

export const setDeActivePoints = () => {
    return {
        type: SET_STATS_DE_ACTIVE_POINTS,
    };
};
