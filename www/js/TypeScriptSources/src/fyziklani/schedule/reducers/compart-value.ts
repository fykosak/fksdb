import {
    ACTION_SET_VISIBILITY,
    ACTION_TOGGLE_CHOOSER,
    IActionSetVisibility,
} from '../actions';

const toggleChooser = (state: ICompactValueState): ICompactValueState => {
    return {
        ...state,
        showChooser: !state.showChooser,
    };
};

const setInitialVisibility = (state: ICompactValueState, action: IActionSetVisibility): ICompactValueState => {
    return {
        ...state,
        showChooser: !action.state,
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
        case ACTION_SET_VISIBILITY:
            return setInitialVisibility(state, action);
        default:
            return state;
    }
};
