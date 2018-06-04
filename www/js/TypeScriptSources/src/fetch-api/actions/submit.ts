import {
    IResponse,
    ISubmitAction,
    ISubmitFailAction,
    ISubmitSuccessAction,
} from '../middleware/interfaces';

export const ACTION_SUBMIT_SUCCESS = '@@fetch-api/SUBMIT_SUCCESS';

export function submitSuccess<D>(data: IResponse<D>, accessKey: string): ISubmitSuccessAction<D> {
    return {
        accessKey,
        data,
        type: ACTION_SUBMIT_SUCCESS,
    };
}

export const ACTION_SUBMIT_FAIL = '@@fetch-api/SUBMIT_FAIL';

export const submitFail = (error, accessKey: string): ISubmitFailAction => {
    return {
        accessKey,
        error,
        type: ACTION_SUBMIT_FAIL,
    };
};

export const ACTION_SUBMIT_START = '@@fetch-api/SUBMIT_START';
export const submitStart = (accessKey: string): ISubmitAction => {
    return {
        accessKey,
        type: ACTION_SUBMIT_START,
    };
};
