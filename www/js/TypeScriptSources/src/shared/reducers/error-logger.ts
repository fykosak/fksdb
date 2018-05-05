import { ACTION_ADD_ERROR } from '../actions/error-logger';
import {
    IMessage,
} from '../interfaces';

export interface IState {
    errors: IMessage[];
}

const addError = (state: IState, action): IState => {
    return {
        ...state,
        errors: [...state.errors, action.error],
    };
};

const initState: IState = {
    errors: [],
};

export const errorLogger = (state: IState = initState, action): IState => {
    switch (action.type) {
        case ACTION_ADD_ERROR:
            return addError(state, action);
        default:
            return state;
    }
};
