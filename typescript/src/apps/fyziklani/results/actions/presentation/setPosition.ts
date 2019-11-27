import { Action } from 'redux';

export interface ActionSetPosition extends Action<string> {
    position: number;
    category?: string;
}

export const ACTION_SET_POSITION = '@@fyziklani/presentation/SET_POSITION';
export const setPosition = (position: number, category: string): ActionSetPosition => {
    return {
        category,
        position,
        type: ACTION_SET_POSITION,
    };
};
