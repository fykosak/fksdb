import {
    ACTION_DRAG_END,
    ACTION_DRAG_START,
    ACTION_DROP_ITEM,
} from '../actions';

const dragStart = (): State => {
    return {
        dragged: true,
    };
};

const dragEnd = (): State => {
    return {
        dragged: false,
    };
};

export const dragNDrop = (state: State = {dragged: false}, action): State => {
    switch (action.type) {
        case ACTION_DRAG_START:
            return dragStart();
        case ACTION_DRAG_END:
        case ACTION_DROP_ITEM:
            return dragEnd();
        default:
            return state;
    }
};

export interface State {
    dragged: boolean;
}
