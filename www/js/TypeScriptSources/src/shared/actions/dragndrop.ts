export const ACTION_DRAG_START = 'ACTION_DRAG_START';

export const dragStart = () => {
    return {
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

export function dropItem<D>(data: D) {
    return {
        data,
        type: ACTION_DROP_ITEM,
    };
}
