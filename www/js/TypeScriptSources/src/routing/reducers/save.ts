import {
    ACTION_REMOVE_UPDATED_TEAMS,
    ACTION_SAVE_ROUTING_FAIL,
    ACTION_SAVE_ROUTING_START,
    ACTION_SAVE_ROUTING_SUCCESS,
} from '../actions/save';
import {
    ACTION_SUBMIT_FAIL,
    ACTION_SUBMIT_START,
    ACTION_SUBMIT_SUCCESS,
} from '../../fetch-api/actions/submit';

const saveStart = (state: IState): IState => {
    return {
        ...state,
        saving: true,
        updatedTeams: [],
    };
};

const saveSuccess = (state: IState, action): IState => {
    const {updatedTeams} = action.data;
    return {
        ...state,
        error: null,
        saving: false,
        updatedTeams,
    };
};

const saveFail = (state: IState, action): IState => {
    return {
        ...state,
        error: action.error,
        saving: false,
    };
};

const removeUpdatesTeams = (state: IState): IState => {
    return {
        ...state,
        updatedTeams: [],
    };
};

export const save = (state = {saving: false, updatedTeams: []}, action): IState => {
    switch (action.type) {
        case ACTION_SAVE_ROUTING_START:
        case ACTION_SUBMIT_START:
            return saveStart(state);
        case ACTION_SAVE_ROUTING_SUCCESS:
        case ACTION_SUBMIT_SUCCESS:
            return saveSuccess(state, action);
        case ACTION_SAVE_ROUTING_FAIL:
        case ACTION_SUBMIT_FAIL:
            return saveFail(state, action);
        case ACTION_REMOVE_UPDATED_TEAMS:
            return removeUpdatesTeams(state);
        default:
            return state;
    }
};

export interface IState {
    error?: any;
    saving: boolean;
    updatedTeams: number[];
}
