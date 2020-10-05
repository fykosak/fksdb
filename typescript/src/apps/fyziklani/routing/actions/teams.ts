import { Action } from 'redux';
import { Team } from '../../helpers/interfaces';

export const ACTION_ADD_TEAMS = 'ACTION_ADD_TEAMS';

export interface ActionAddTeams extends Action {
    teams: Team[];
}

export const addTeams = (teams: Team[]): ActionAddTeams => {
    return {
        teams,
        type: ACTION_ADD_TEAMS,
    };
};

export const ACTION_REMOVE_PLACE = 'ACTION_REMOVE_PLACE';

export interface ActionRemoveTeamPlace extends Action {
    teamId: number;
}

export const removeTeamPlace = (teamId: number): ActionRemoveTeamPlace => {
    return {
        teamId,
        type: ACTION_REMOVE_PLACE,
    };
};
