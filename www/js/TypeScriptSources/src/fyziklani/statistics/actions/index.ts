export const ACTION_SET_ACTIVE_POINTS = '@@fyziklani/ACTION_SET_ACTIVE_POINTS';

export const setActivePoints = (activePoints: number) => {
    return {
        activePoints,
        type: ACTION_SET_ACTIVE_POINTS,
    };
};

export const ACTION_SET_TEAM_ID = '@@fyziklani/ACTION_SET_TEAM_ID';

export const setTeamId = (teamId: number) => {
    return {
        teamId,
        type: ACTION_SET_TEAM_ID,
    };
};
