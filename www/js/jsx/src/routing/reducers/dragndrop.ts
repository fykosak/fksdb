import { ACTION_DRAG_START } from '../actions/dragndrop';

const dragStart = (state: IState, action): IState => {
    const { teamID } = action;
    return {
        ...state,
        draggedTeamID: teamID,
    };
};

export const dragNDrop = (state: IState = {}, action): IState => {
    switch (action.type) {
        case ACTION_DRAG_START:
            return dragStart(state, action);
        default:
            return state;
    }
};
export interface IState {
    draggedTeamID?: number;
}
