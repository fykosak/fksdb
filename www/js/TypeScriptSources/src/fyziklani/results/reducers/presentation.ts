import {
    ACTION_SET_POSITION,
    IActionSetPosition,
} from '../actions/Presentation/SetPosition';
import {
    ACTION_SET_COLS,
    IActionSetCols,
} from '../actions/Presentation/SetCols';
import {
    ACTION_SET_ROWS,
    IActionSetRows,
} from '../actions/Presentation/SetRows';
import {
    ACTION_SET_DELAY,
    IActionSetDelay,
} from '../actions/Presentation/SetDelay';


export interface IState {
    position: number;
    cols: number;
    rows: number;
    delay: number;
}

const initialState: IState = {
    cols: 2,
    delay: 10 * 1000,
    position: 0,
    rows: 20,
};

const setCols = (state: IState, action: IActionSetCols): IState => {
    return {
        ...state,
        cols: action.cols,
    };
};
const setRows = (state: IState, action: IActionSetRows): IState => {
    return {
        ...state,
        rows: action.rows,
    };
};
const setDelay = (state: IState, action: IActionSetDelay): IState => {
    return {
        ...state,
        delay: action.delay,
    };
};
const setNewPosition = (state: IState, action: IActionSetPosition): IState => {
    return {
        ...state,
        position: action.position,
    };
};

export const presentation = (state: IState = initialState, action): IState => {

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
