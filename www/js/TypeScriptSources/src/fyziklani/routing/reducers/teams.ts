import { ACTION_SUBMIT_SUCCESS } from '../../../fetch-api/actions/submit';
import { IActionSubmitSuccess } from '../../../fetch-api/middleware/interfaces';
import { ITeam } from '../../helpers/interfaces';
import {
    ACTION_DROP_ITEM,
    IActionDropItem,
} from '../actions/dragndrop';
import { ACTION_REMOVE_UPDATED_TEAMS } from '../actions/save';
import {
    ACTION_ADD_TEAMS,
    ACTION_REMOVE_PLACE,
    IActionAddTeams,
    IActionRemoveTeamPlace,
} from '../actions/teams';
import {
    IResponseData,
    IRoutingDragNDropData,
} from '../middleware/interfaces';

export interface IFyziklaniRoutingTeamsState {
    availableTeams: ITeam[];
    updatedTeams: number[];
}

function routeTeam(state: IFyziklaniRoutingTeamsState, action: IActionDropItem<IRoutingDragNDropData>): IFyziklaniRoutingTeamsState {
    const {teamId, place: {x, y, roomId}} = action.data;
    const newTeams = state.availableTeams.map((team) => {
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
    return {
        ...state,
        availableTeams: newTeams,
    };
}

const addTeams = (state: IFyziklaniRoutingTeamsState, action: IActionAddTeams): IFyziklaniRoutingTeamsState => {
    return {
        ...state,
        availableTeams: action.teams,
    };
};

const removePlace = (state: IFyziklaniRoutingTeamsState, action: IActionRemoveTeamPlace): IFyziklaniRoutingTeamsState => {
    const {teamId} = action;

    const newTeams = state.availableTeams.map((team) => {
        if (team.teamId !== teamId) {
            return team;
        }
        return {
            ...team,
            roomId: null,
            x: null,
            y: null,
        };
    });
    return {
        ...state,
        availableTeams: newTeams,
    };
};

const removeUpdatedTeams = (state: IFyziklaniRoutingTeamsState): IFyziklaniRoutingTeamsState => {
    return {
        ...state,
        updatedTeams: [],
    };
};

const addUpdatedTeams = (state: IFyziklaniRoutingTeamsState, action: IActionSubmitSuccess<IResponseData>): IFyziklaniRoutingTeamsState => {
    return {
        ...state,
        updatedTeams: action.data.responseData.updatedTeams,
    };
};

const initialState = {
    availableTeams: [],
    updatedTeams: [],
};
export const teams = (state: IFyziklaniRoutingTeamsState = initialState, action): IFyziklaniRoutingTeamsState => {
    switch (action.type) {
        case ACTION_ADD_TEAMS:
            return addTeams(state, action);
        case ACTION_DROP_ITEM:
            return routeTeam(state, action);
        case ACTION_REMOVE_PLACE:
            return removePlace(state, action);
        case ACTION_SUBMIT_SUCCESS:
            return addUpdatedTeams(state, action);
        case ACTION_REMOVE_UPDATED_TEAMS:
            return removeUpdatedTeams(state);
        default:
            return state;
    }
};
