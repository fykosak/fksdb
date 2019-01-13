import { Action } from 'redux';

export interface IActionSetRows extends Action<string> {
    rows: number;
}

export const ACTION_SET_ROWS = '@@fyziklani/presentation/SET_ROWS';
export const setRows = (rows: number): IActionSetRows => {
    return {
        rows,
        type: ACTION_SET_ROWS,
    };
};
