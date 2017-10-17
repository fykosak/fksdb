/**
 * Created by miso on 16.10.2017.
 */
import {
    ACTION_SET_CONTROL_INPUT,
    ACTION_SET_TASK_INPUT,
    ACTION_SET_TEAM_INPUT,
} from '../actions/index';

export interface IState {
    taskInput?: HTMLInputElement;
    teamInput ?: HTMLInputElement;
    controlInput ?: HTMLInputElement;
}

const setTaskInput = (state: IState, action): IState => {
    return {
        ...state,
        taskInput: action.input,
    };
};
const setTeamInput = (state: IState, action): IState => {
    return {
        ...state,
        teamInput: action.input,
    };
};
const setControlInput = (state: IState, action): IState => {
    return {
        ...state,
        controlInput: action.input,
    };
};

const initState: IState = {};

export const nodes = (state: IState = initState, action): IState => {
    switch (action.type) {
        case ACTION_SET_CONTROL_INPUT:
            return setControlInput(state, action);
        case ACTION_SET_TASK_INPUT:
            return setTaskInput(state, action);
        case ACTION_SET_TEAM_INPUT:
            return setTeamInput(state, action);
        default:
            return state;
    }
};
