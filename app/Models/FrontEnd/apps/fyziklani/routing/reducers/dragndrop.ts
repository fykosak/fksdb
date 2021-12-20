import { ACTION_DRAG_END, ACTION_DROP_ITEM } from 'FKSDB/Models/FrontEnd/shared/dragndrop';

export interface State<Data> {
    data?: Data;
}

const dragEnd = <Data>(state: State<Data>): State<Data> => {
    return {
        ...state,
        data: null,
    };
}

export const dragNDrop = <Data>(state: State<Data> = {data: null}, action): State<Data> => {
    switch (action.type) {
        case ACTION_DROP_ITEM:
        case ACTION_DRAG_END:
            return dragEnd<Data>(state);
        default:
            return state;
    }
}
