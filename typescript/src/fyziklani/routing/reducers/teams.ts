import { ACTION_SUBMIT_SUCCESS } from '../../../fetch-api/actions/submit';
import { ActionSubmitSuccess } from '../../../fetch-api/middleware/interfaces';
import { Team } from '../../helpers/interfaces';
import {
    ACTION_DROP_ITEM,
    ActionDropItem,
} from '../actions/dragndrop';
import { ACTION_REMOVE_UPDATED_TEAMS } from '../actions/save';
import {
    ACTION_ADD_TEAMS,
    ACTION_REMOVE_PLACE,
    ActionAddTeams,
    ActionRemoveTeamPlace,
} from '../actions/teams';
import {
    DragNDropData,
    ResponseData,
} from '../middleware/interfaces';

export interface State {
    availableTeams: Team[];
    updatedTeams: number[];
}

function routeTeam(state: State, action: ActionDropItem<DragNDropData>): State {
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

const addTeams = (state: State, action: ActionAddTeams): State => {
    return {
        ...state,
        availableTeams: action.teams,
    };
};

const removePlace = (state: State, action: ActionRemoveTeamPlace): State => {
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

const removeUpdatedTeams = (state: State): State => {
    return {
        ...state,
        updatedTeams: [],
    };
};

const addUpdatedTeams = (state: State, action: ActionSubmitSuccess<ResponseData>): State => {
    return {
        ...state,
        updatedTeams: action.data.responseData.updatedTeams,
    };
};

const initialState = {
    availableTeams: [],
    updatedTeams: [],
};
export const teams = (state: State = initialState, action): State => {
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
