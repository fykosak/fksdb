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

export interface ActionSetDelay extends Action<string> {
    delay: number;
}

export const ACTION_SET_DELAY = '@@fyziklani/presentation/SET_DELAY';

export const setDelay = (delay: number): ActionSetDelay => {
    return {
        delay,
        type: ACTION_SET_DELAY,
    };
};

export interface ActionSetPosition extends Action<string> {
    position: number;
    category: string;
}

export const ACTION_SET_POSITION = '@@fyziklani/presentation/SET_POSITION';

export const setPosition = (position: number, category: string): ActionSetPosition => {
    return {
        category,
        position,
        type: ACTION_SET_POSITION,
    };
};

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
