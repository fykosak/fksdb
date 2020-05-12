import { Action } from 'redux';

export const ACTION_DRAG_START = 'ACTION_DRAG_START';

export const dragStart = (): Action<string> => {
    return {
        type: ACTION_DRAG_START,
    };
};

export const ACTION_DRAG_END = 'ACTION_DRAG_END';

export const dragEnd = (): Action<string> => {
    return {
        type: ACTION_DRAG_END,
    };
};

export const ACTION_DROP_ITEM = 'ACTION_DROP_ITEM';

export interface ActionDropItem<D> extends Action<string> {
    data: D;
}

export function dropItem<D>(data: D): ActionDropItem<D> {
    return {
        data,
        type: ACTION_DROP_ITEM,
    };
}

export const ACTION_ADD_ERROR = 'ACTION_ADD_ERROR';

export interface ActionAddError extends Action<string> {
    error: any;
}

export const addError = (error): ActionAddError => {
    return {
        error,
        type: ACTION_ADD_ERROR,
    };
};
