export const ACTION_DRAG_START = 'ACTION_DRAG_START';

export const dragStart = (teamID: number) => {
    return {
        teamID,
        type: ACTION_DRAG_START,
    };
};

export const ACTION_DRAG_END = 'ACTION_DRAG_END';

export const dragEnd = () => {
    return {
        type: ACTION_DRAG_END,
    };
};

export const ACTION_DROP_ITEM = 'ACTION_DROP_ITEM';

export const dropItem = (teamID, place) => {
    return {
        place,
        teamID,
        type: ACTION_DROP_ITEM,
    };
};

