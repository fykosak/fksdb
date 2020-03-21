import {
    ActionSubmit,
    ActionSubmitFail,
    ActionSubmitSuccess,
    Response,
} from '../middleware/interfaces';
import jqXHR = JQuery.jqXHR;

export const ACTION_SUBMIT_SUCCESS = 'ACTION_SUBMIT_SUCCESS';

export function submitSuccess<D>(data: Response<D>, accessKey: string): ActionSubmitSuccess<D> {
    return {
        accessKey,
        data,
        type: ACTION_SUBMIT_SUCCESS,
    };
}

export const ACTION_SUBMIT_FAIL = 'ACTION_SUBMIT_FAIL';

export function submitFail<T= any>(error: jqXHR<T>, accessKey: string): ActionSubmitFail<T> {
    return {
        accessKey,
        error,
        type: ACTION_SUBMIT_FAIL,
    };
}

export const ACTION_SUBMIT_START = 'ACTION_SUBMIT_START';
export const submitStart = (accessKey: string): ActionSubmit => {
    return {
        accessKey,
        type: ACTION_SUBMIT_START,
    };
};
