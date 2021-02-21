import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import { Action } from 'redux';

export const ACTION_ADD_TEAMS = 'ACTION_ADD_TEAMS';

export interface ActionAddTeams extends Action {
    teams: ModelFyziklaniTeam[];
}

export const addTeams = (teams: ModelFyziklaniTeam[]): ActionAddTeams => {
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
