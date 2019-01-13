import { Action } from 'redux';

export interface IActionSetCols extends Action<string> {
    cols: number;
}

export const ACTION_SET_COLS = '@@fyziklani/presentation/SET_COLS';
export const setCols = (cols: number): IActionSetCols => {
    return {
        cols,
        type: ACTION_SET_COLS,
    };
};
