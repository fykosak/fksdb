import { Action } from 'redux';

export const ACTION_DRAG_START = '@@drag-n-drop/ACTION_DRAG_START';

export interface ActionDragStart<D> extends Action {
    data: D;
}

export function dragStart<D>(data: D): ActionDragStart<D> {
    return {
        data,
        type: ACTION_DRAG_START,
    };
}

export const ACTION_DRAG_END = '@@drag-n-drop/ACTION_DRAG_END';

export const dragEnd = (): Action => {
    return {
        type: ACTION_DRAG_END,
    };
};

export interface ActionDropItem<D> extends Action {
    data: D;
}

export const ACTION_DROP_ITEM = '@@drag-n-drop/ACTION_DROP_ITEM';

export function dropItem<D>(data: D): ActionDropItem<D> {
    return {
        data,
        type: ACTION_DROP_ITEM,
    };
}
