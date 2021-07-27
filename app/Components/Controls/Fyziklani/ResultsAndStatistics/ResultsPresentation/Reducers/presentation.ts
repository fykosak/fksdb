import {
    ACTION_SET_COLS,
    ACTION_SET_DELAY,
    ACTION_SET_POSITION,
    ACTION_SET_ROWS,
    ActionSetCols,
    ActionSetDelay,
    ActionSetPosition,
    ActionSetRows,
} from '../actions';

export interface State {
    position: number;
    cols: number;
    rows: number;
    delay: number;
    category?: string;
}

const initialState: State = {
    category: null,
    cols: 2,
    delay: 10 * 1000,
    position: 0,
    rows: 20,
};

const setCols = (state: State, action: ActionSetCols): State => {
    return {
        ...state,
        cols: action.cols,
    };
};
const setRows = (state: State, action: ActionSetRows): State => {
    return {
        ...state,
        rows: action.rows,
    };
};
const setDelay = (state: State, action: ActionSetDelay): State => {
    return {
        ...state,
        delay: action.delay,
    };
};
const setNewPosition = (state: State, action: ActionSetPosition): State => {
    return {
        ...state,
        category: action.category,
        position: action.position,
    };
};

export const presentation = (state: State = initialState, action): State => {

    switch (action.type) {

        case ACTION_SET_POSITION:
            return setNewPosition(state, action);
        case ACTION_SET_COLS:
            return setCols(state, action);
        case ACTION_SET_ROWS:
            return setRows(state, action);
        case ACTION_SET_DELAY:
            return setDelay(state, action);
        default:
            return state;
    }
};
