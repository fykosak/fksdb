import { dispatchNetteFetch } from '@fetchApi/middleware/fetch';
import {
    Action,
    Dispatch,
} from 'redux';
import { UploadDataItem } from '../middleware/uploadDataItem';
import { Store } from '../reducers';

export const NEW_DATA_ARRIVED = 'NEW_DATA_ARRIVED';

export interface ActionNewDataArrived extends Action<string> {
    data: UploadDataItem;
}

export const newDataArrived = (data: UploadDataItem): ActionNewDataArrived => {
    return {
        data,
        type: NEW_DATA_ARRIVED,
    };
};

export const deleteUploadedFile = (dispatch: Dispatch<Action<string>>, accessKey: string, submitId: number, link: string) => {
    return dispatchNetteFetch<{ submitId: number }, any, Store>(accessKey, dispatch, {
        act: 'revoke',
        requestData: {
            submitId,
        },
    }, () => null, () => null, link);
};
