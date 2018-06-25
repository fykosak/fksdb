import { ACTION_SET_DEFINITIONS } from '../actions/definitions';
import { IDefinitionsState } from '../interfaces';

function setDefinitions(state: IDefinitionsState, action): IDefinitionsState {
    return {
        ...state,
        ...action.data,
    };
}

export function definitions(state: IDefinitionsState, event): IDefinitionsState {
    switch (event.type) {
        case ACTION_SET_DEFINITIONS:
            return setDefinitions(state, event);
        default:
            return state;
    }
}
