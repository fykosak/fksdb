import { Action } from 'redux';

export const ACTION_SET_HARD_VISIBLE = '@@fyziklani/ACTION_SET_HARD_VISIBLE';

export interface ActionSetHardVisible extends Action<string> {
    hardVisible: boolean;
}

export const setHardVisible = (state: boolean): ActionSetHardVisible => {
    return {
        hardVisible: state,
        type: ACTION_SET_HARD_VISIBLE,
    };
};
