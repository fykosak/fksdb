import { Action } from 'redux';
import { State } from '../reducers/stats';

export const ACTION_SET_NEW_STATE = '@@game/ACTION_SET_NEW_STATE'

export interface ActionSetNewState extends Action<string> {
    data: State;
}

export const setNewState = (data: State): ActionSetNewState => {
    return {
        data,
        type: ACTION_SET_NEW_STATE,
    };
};
