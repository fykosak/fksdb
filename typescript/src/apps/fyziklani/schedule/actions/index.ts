import { Action } from 'redux';

export const ACTION_TOGGLE_CHOOSER = 'ACTION_TOGGLE_CHOOSER';
export const toggleChooser = (): Action<string> => {
    return {
        type: ACTION_TOGGLE_CHOOSER,
    };
};

export const ACTION_SET_VISIBILITY = 'ACTION_SET_VISIBILITY';

export interface ActionSetVisibility extends Action<string> {
    state: boolean;
}

export const setVisibility = (state: boolean): ActionSetVisibility => {
    return {
        state,
        type: ACTION_SET_VISIBILITY,
    };
};
