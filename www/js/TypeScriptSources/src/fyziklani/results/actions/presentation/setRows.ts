import { Action } from 'redux';

export interface ActionSetRows extends Action<string> {
    rows: number;
}

export const ACTION_SET_ROWS = '@@fyziklani/presentation/SET_ROWS';
export const setRows = (rows: number): ActionSetRows => {
    return {
        rows,
        type: ACTION_SET_ROWS,
    };
};
