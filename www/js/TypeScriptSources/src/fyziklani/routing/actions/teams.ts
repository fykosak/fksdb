import { ITeam } from '../../helpers/interfaces';
import { Action } from 'redux';

export const ACTION_ADD_TEAMS = 'ACTION_ADD_TEAMS';

export interface IActionAddTeams extends Action {
    teams: ITeam[];
}

export const addTeams = (teams: ITeam[]): IActionAddTeams => {
    return {
        teams,
        type: ACTION_ADD_TEAMS,
    };
};

export const ACTION_REMOVE_PLACE = 'ACTION_REMOVE_PLACE';

export interface IActionRemoveTeamPlace extends Action {
    teamId: number;
}

export const removeTeamPlace = (teamId: number): IActionRemoveTeamPlace => {
    return {
        teamId,
        type: ACTION_REMOVE_PLACE,
    };
};
