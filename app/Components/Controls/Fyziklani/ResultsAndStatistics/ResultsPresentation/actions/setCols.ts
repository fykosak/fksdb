import { Action } from 'redux';

export interface ActionSetCols extends Action<string> {
    cols: number;
}

export const ACTION_SET_COLS = '@@fyziklani/presentation/SET_COLS';

export const setCols = (cols: number): ActionSetCols => {
    return {
        cols,
        type: ACTION_SET_COLS,
    };
};
