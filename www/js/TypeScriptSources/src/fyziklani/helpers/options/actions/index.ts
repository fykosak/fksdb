import { Action } from 'redux';

export const ACTION_SET_HARD_VISIBLE = '@@fyziklani/ACTION_SET_HARD_VISIBLE';

export interface IActionSetHardVisible extends Action {
    hardVisible: boolean;
}

export const setHardVisible = (state: boolean): IActionSetHardVisible => {
    return {
        hardVisible: state,
        type: ACTION_SET_HARD_VISIBLE,
    };
};
