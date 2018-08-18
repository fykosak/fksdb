import {
    ACTION_DRAG_END,
    ACTION_DRAG_START,
    ACTION_DROP_ITEM,
} from '../actions/dragndrop';

const dragStart = (state: IState, action): IState => {
    const { teamId } = action;
    return {
        ...state,
        draggedTeamId: teamId,
    };
};

const dragEnd = (state: IState): IState => {
    return {
        ...state,
        draggedTeamId: null,
    };
};

export const dragNDrop = (state: IState = {}, action): IState => {
    switch (action.type) {
        case ACTION_DRAG_START:
            return dragStart(state, action);
        case ACTION_DROP_ITEM:
        case ACTION_DRAG_END:
            return dragEnd(state);
        default:
            return state;
    }
};

export interface IState {
    draggedTeamId?: number;
}
