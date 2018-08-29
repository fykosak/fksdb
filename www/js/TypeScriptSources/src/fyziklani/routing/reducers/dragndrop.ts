import {
    ACTION_DRAG_END,
    ACTION_DRAG_START,
    ACTION_DROP_ITEM,
    IActionDragStart,
} from '../actions/dragndrop';

export interface IState<D> {
    data?: D;
}

function dragStart<D>(state: IState<D>, action: IActionDragStart<D>): IState<D> {
    const {data} = action;
    return {
        ...state,
        data,
    };
}

function dragEnd<D>(state: IState<D>): IState<D> {
    return {
        ...state,
        data: null,
    };
}

export function dragNDrop<D = any>(state: IState<D> = {data: null}, action): IState<D> {
    switch (action.type) {
        case ACTION_DRAG_START:
            return dragStart<D>(state, action);
        case ACTION_DROP_ITEM:
        case ACTION_DRAG_END:
            return dragEnd<D>(state);
        default:
            return state;
    }
}
