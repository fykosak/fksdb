import { Action } from 'redux';
import { Message } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';

export const ACTION_ADD_ERROR = 'ACTION_ADD_ERROR';

export interface ActionAddError extends Action<string> {
    error: Message;
}

export const addError = (error: Message): ActionAddError => {
    return {
        error,
        type: ACTION_ADD_ERROR,
    };
};

export const ACTION_DRAG_START = '@@drag-n-drop/ACTION_DRAG_START';

export const ACTION_DRAG_END = '@@drag-n-drop/ACTION_DRAG_END';

export const ACTION_DROP_ITEM = '@@drag-n-drop/ACTION_DROP_ITEM';

export interface ActionDropItem<Data> extends Action<string> {
    data: Data;
}

export const dropItem = <Data>(data: Data): ActionDropItem<Data> => {
    return {
        data,
        type: ACTION_DROP_ITEM,
    };
}

