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
