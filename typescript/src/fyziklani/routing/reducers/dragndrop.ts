import {
    ACTION_DRAG_END,
    ACTION_DRAG_START,
    ACTION_DROP_ITEM,
    ActionDragStart,
} from '../actions/dragndrop';

export interface State<D> {
    data?: D;
}

function dragStart<D>(state: State<D>, action: ActionDragStart<D>): State<D> {
    const {data} = action;
    return {
        ...state,
        data,
    };
}

function dragEnd<D>(state: State<D>): State<D> {
    return {
        ...state,
        data: null,
    };
}

export function dragNDrop<D = any>(state: State<D> = {data: null}, action): State<D> {
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
