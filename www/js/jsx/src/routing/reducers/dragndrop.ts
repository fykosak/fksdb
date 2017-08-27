import { ACTION_DRAG_START } from '../actions/dragndrop';

const dragStart = (state, action) => {
    const { teamID } = action;
    return {
        ...state,
        draggedTeamID: teamID,
    };
};

export const dragNDrop = (state = {}, action) => {
    switch (action.type) {
        case ACTION_DRAG_START:
            return dragStart(state, action);
        default:
            return state;
    }
};