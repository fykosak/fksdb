import { ACTION_ADD_ERROR } from 'FKSDB/Components/Controls/AjaxSubmit/actions';
import { ACTION_FETCH_START } from 'FKSDB/Models/FrontEnd/Fetch/actions';
import { Message } from 'FKSDB/Models/FrontEnd/Fetch/interfaces';

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
        case ACTION_FETCH_START:
            return clearErrors(state);
        default:
            return state;
    }
};
