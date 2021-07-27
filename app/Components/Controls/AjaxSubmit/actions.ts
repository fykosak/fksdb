import { Action } from 'redux';

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
