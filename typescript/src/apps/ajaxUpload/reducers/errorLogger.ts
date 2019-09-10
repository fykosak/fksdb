import { Message } from '@fetchApi/middleware/interfaces';
import { ACTION_ADD_ERROR } from '../actions';

export interface State {
    errors: Message[];
}

const addError = (state: State, action): State => {
    return {
        ...state,
        errors: [...state.errors, action.error],
    };
};

const initState: State = {
    errors: [],
};

export const errorLogger = (state: State = initState, action): State => {
    switch (action.type) {
        case ACTION_ADD_ERROR:
            return addError(state, action);
        default:
            return state;
    }
};
