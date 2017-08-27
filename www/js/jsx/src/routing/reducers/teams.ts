import { ACTION_DROP_ITEM } from '../actions/dragndrop';
import {
    ACTION_ADD_TEAMS,
    ACTION_REMOVE_PLACE,
} from '../actions/teams';
import { ITeam } from '../interfaces';

const routeTeam = (state: any[], action): ITeam[] => {
    const { teamID, place: { x, y, room } } = action;
    return state.map((team) => {
        if (team.teamID !== teamID) {
            return team;
        }
        return {
            ...team,
            room,
            x,
            y,
        };
    });
};

const addTeams = (state: ITeam[], action): ITeam[] => {
    return action.teams;
};

const removePlace = (state, action): ITeam[] => {
    const { teamID } = action;
    return state.map((team) => {
        if (team.teamID !== teamID) {
            return team;
        }
        delete team.x;
        delete team.y;
        delete team.room;
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
