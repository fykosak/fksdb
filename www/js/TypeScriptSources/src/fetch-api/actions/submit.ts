import {
    IActionSubmit,
    IActionSubmitFail,
    IActionSubmitSuccess,
    IResponse,
} from '../middleware/interfaces';
import jqXHR = JQuery.jqXHR;

export const ACTION_SUBMIT_SUCCESS = 'ACTION_SUBMIT_SUCCESS';

export function submitSuccess<D>(data: IResponse<D>, accessKey: string): IActionSubmitSuccess<D> {
    return {
        accessKey,
        data,
        type: ACTION_SUBMIT_SUCCESS,
    };
}

export const ACTION_SUBMIT_FAIL = 'ACTION_SUBMIT_FAIL';

export function submitFail<T= any>(error: jqXHR<T>, accessKey: string): IActionSubmitFail<T> {
    return {
        accessKey,
        error,
        type: ACTION_SUBMIT_FAIL,
    };
}

export const ACTION_SUBMIT_START = 'ACTION_SUBMIT_START';
export const submitStart = (accessKey: string): IActionSubmit => {
    return {
        accessKey,
        type: ACTION_SUBMIT_START,
    };
};
