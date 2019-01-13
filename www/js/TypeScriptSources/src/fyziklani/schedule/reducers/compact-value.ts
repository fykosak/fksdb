import {
    ACTION_SET_VISIBILITY,
    ACTION_TOGGLE_CHOOSER,
    ActionSetVisibility,
} from '../actions';

const toggleChooser = (state: State): State => {
    return {
        ...state,
        showChooser: !state.showChooser,
    };
};

const setInitialVisibility = (state: State, action: ActionSetVisibility): State => {
    return {
        ...state,
        showChooser: !action.state,
    };
};

export interface State {
    showChooser: boolean;
}

const initState = {
    showChooser: true,
};

export const compactValue = (state: State = initState, action): State => {
    switch (action.type) {
        case ACTION_TOGGLE_CHOOSER:
            return toggleChooser(state);
        case ACTION_SET_VISIBILITY:
            return setInitialVisibility(state, action);
        default:
            return state;
    }
};
