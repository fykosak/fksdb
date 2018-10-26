import {
    ACTION_SET_INITIAL_DATA,
    IActionSetInitialData,
} from '../../../input-connector/actions';
import { ACTION_TOGGLE_CHOOSER } from '../actions';

const toggleChooser = (state: ICompactValueState): ICompactValueState => {
    return {
        ...state,
        showChooser: !state.showChooser,
    };
};

const setInitialVisibility = (state: ICompactValueState, action: IActionSetInitialData): ICompactValueState => {
    return {
        ...state,
        showChooser: !action.data,
    };
};

export interface ICompactValueState {
    showChooser: boolean;
}

const initState = {
    showChooser: true,
};

export const compactValue = (state: ICompactValueState = initState, action): ICompactValueState => {
    switch (action.type) {
        case ACTION_TOGGLE_CHOOSER:
            return toggleChooser(state);
        case ACTION_SET_INITIAL_DATA:
            return setInitialVisibility(state, action);
        default:
            return state;
    }
};
