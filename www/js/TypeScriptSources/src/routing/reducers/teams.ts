import { ITeam } from '../../shared/interfaces';
import { ACTION_DROP_ITEM } from '../actions/dragndrop';
import {
    ACTION_ADD_TEAMS,
    ACTION_REMOVE_PLACE,
} from '../actions/teams';

const routeTeam = (state: any[], action): ITeam[] => {
    const { teamId, place: { x, y, roomId } } = action;
    return state.map((team) => {
        if (team.teamId !== teamId) {
            return team;
        }
        return {
            ...team,
            roomId,
            x,
            y,
        };
    });
};

const addTeams = (state: ITeam[], action): ITeam[] => {
    return action.teams;
};

const removePlace = (state, action): ITeam[] => {
    const { teamId } = action;
    return state.map((team) => {
        if (team.teamId !== teamId) {
            return team;
        }
        team.x = null;
        team.y = null;
        team.roomId = null;
        return {
            ...team,
        };
    });
};

export const teams = (state: ITeam[] = [], action): ITeam[] => {
    switch (action.type) {
        case ACTION_ADD_TEAMS:
            return addTeams(state, action);
        case ACTION_DROP_ITEM:
            return routeTeam(state, action);
        case ACTION_REMOVE_PLACE:
            return removePlace(state, action);
        default:
            return state;
    }
};
