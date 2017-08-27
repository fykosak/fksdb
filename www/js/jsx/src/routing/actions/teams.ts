export const ACTION_ADD_TEAMS = 'ACTION_ADD_TEAMS';

export const addTeams = (teams) => {
    return {
        teams,
        type: ACTION_ADD_TEAMS,
    };
};

export const ACTION_REMOVE_PLACE = 'ACTION_REMOVE_PLACE';

export const removeTeamPlace = (teamID) => {
    return {
        teamID,
        type: ACTION_REMOVE_PLACE,
    };
};
