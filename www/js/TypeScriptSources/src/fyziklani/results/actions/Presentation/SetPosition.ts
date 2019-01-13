import { Action } from 'redux';

export interface IActionSetPosition extends Action<string> {
    position: number;
}

export const ACTION_SET_POSITION = '@@fyziklani/presentation/SET_POSITION';
export const setPosition = (position: number) => {
    return {
        position,
        type: ACTION_SET_POSITION,
    };
};
