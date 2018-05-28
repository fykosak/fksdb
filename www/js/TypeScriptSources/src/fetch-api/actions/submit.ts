import {
    IResponse,
    ISubmitAction,
    ISubmitFailAction,
    ISubmitSuccessAction,
} from '../middleware/interfaces';

export const ACTION_SUBMIT_SUCCESS = 'ACTION_SUBMIT_SUCCESS';

export function submitSuccess<D>(data: IResponse<D>, accessKey: string): ISubmitSuccessAction<D> {
    return {
        accessKey,
        data,
        type: ACTION_SUBMIT_SUCCESS,
    };
}

export const ACTION_SUBMIT_FAIL = 'ACTION_SUBMIT_FAIL';

export const submitFail = (error, accessKey: string): ISubmitFailAction => {
    return {
        accessKey,
        error,
        type: ACTION_SUBMIT_FAIL,
    };
};

export const ACTION_SUBMIT_START = 'ACTION_SUBMIT_START';
export const submitStart = (accessKey: string): ISubmitAction => {
    return {
        accessKey,
        type: ACTION_SUBMIT_START,
    };
};
