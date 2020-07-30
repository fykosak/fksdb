import { Action } from 'redux';

export const ACTION_DRAG_START = '@@drag-n-drop/ACTION_DRAG_START';

export const dragStart = (): Action<string> => {
    return {
        type: ACTION_DRAG_START,
    };
};

export const ACTION_DRAG_END = '@@drag-n-drop/ACTION_DRAG_END';

export const dragEnd = (): Action<string> => {
    return {
        type: ACTION_DRAG_END,
    };
};

export const ACTION_DROP_ITEM = '@@drag-n-drop/ACTION_DROP_ITEM';

export interface ActionDropItem<D> extends Action<string> {
    data: D;
}

export function dropItem<D>(data: D): ActionDropItem<D> {
    return {
        data,
        type: ACTION_DROP_ITEM,
    };
}
