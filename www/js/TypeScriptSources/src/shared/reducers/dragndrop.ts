import {
    ACTION_DRAG_END,
    ACTION_DRAG_START,
} from '../actions/dragndrop';

const dragStart = (): IState => {
    return {
        dragged: true,
    };
};

const dragEnd = (): IState => {
    return {
        dragged: false,
    };
};

export const dragNDrop = (state: IState = {dragged: false}, action): IState => {
    switch (action.type) {
        case ACTION_DRAG_START:
            return dragStart();
        case ACTION_DRAG_END:
            return dragEnd();
        default:
            return state;
    }
};

export interface IState {
    dragged: boolean;
}
