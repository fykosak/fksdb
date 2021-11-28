import {
    ACTION_FETCH_SUCCESS,
    ActionFetchSuccess,
} from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/actions';
import { DataResponse } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import { ACTION_DROP_ITEM, ActionDropItem } from 'FKSDB/Models/FrontEnd/shared/dragndrop';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
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
    availableTeams: ModelFyziklaniTeam[];
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

const fetchSuccess = (state: State, action: ActionFetchSuccess<DataResponse<ResponseData>>): State => {
    return {
        ...state,
        updatedTeams: action.data.data.updatedTeams,
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
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, action);
        default:
            return state;
    }
};
