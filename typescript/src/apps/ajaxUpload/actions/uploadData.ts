import { dispatchNetteFetch } from '@fetchApi/middleware/fetch';
import {
    Action,
    Dispatch,
} from 'redux';
import { Store } from '../reducers';

export const deleteUploadedFile = (dispatch: Dispatch<Action<string>>, accessKey: string, submitId: number, link: string) => {
    return dispatchNetteFetch<{ submitId: number }, any, Store>(accessKey, dispatch, {
        act: 'revoke',
        requestData: {
            submitId,
        },
    }, () => null, () => null, link);
};

export const downloadUploadedFile = (dispatch: Dispatch<Action<string>>, accessKey: string, submitId: number, link: string) => {
    return dispatchNetteFetch<{ submitId: number }, any, Store>(accessKey, dispatch, {
        act: 'download',
        requestData: {
            submitId,
        },
    }, () => null, () => null, link);
};
