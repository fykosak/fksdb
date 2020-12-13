import { ACTION_DRAG_END, ACTION_DROP_ITEM } from '@FKSDB/Model/FrontEnd/shared/dragndrop';

export interface State<D> {
    data?: D;
}

function dragEnd<D>(state: State<D>): State<D> {
    return {
        ...state,
        data: null,
    };
}

export function dragNDrop<D = any>(state: State<D> = {data: null}, action): State<D> {
    switch (action.type) {
        case ACTION_DROP_ITEM:
        case ACTION_DRAG_END:
            return dragEnd<D>(state);
        default:
            return state;
    }
}
