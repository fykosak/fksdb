import { Message } from '@fetchApi/middleware/interfaces';
import { ACTION_ADD_ERROR } from '../actions';
import { ACTION_SUBMIT_START } from '@fetchApi/actions/submit';

export interface State {
    errors: Message[];
}

const addError = (state: State, action): State => {
    return {
        ...state,
        errors: [...state.errors, action.error],
    };
};
const clearErrors = (state: State): State => {
    return {
        ...state,
        errors: [],
    };
};

const initState: State = {
    errors: [],
};

export const errorLogger = (state: State = initState, action): State => {
    switch (action.type) {
        case ACTION_ADD_ERROR:
            return addError(state, action);
        case ACTION_SUBMIT_START:
            return clearErrors(state);
        default:
            return state;
    }
};
