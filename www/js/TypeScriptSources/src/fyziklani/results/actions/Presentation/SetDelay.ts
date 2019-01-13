import { Action } from 'redux';

export interface IActionSetDelay extends Action<string> {
    delay: number;
}

export const ACTION_SET_DELAY = '@@fyziklani/presentation/SET_DELAY';
export const setDelay = (delay: number): IActionSetDelay => {
    return {
        delay,
        type: ACTION_SET_DELAY,
    };
};
