import { ACTION_ADD_ERROR } from 'FKSDB/Components/Controls/AjaxSubmit/actions';
import { ACTION_FETCH_START } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/actions';
import { Message } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';

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
