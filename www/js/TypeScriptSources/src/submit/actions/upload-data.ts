import { Dispatch } from 'redux';
import { dispatchNetteFetch } from '../../fetch-api/middleware/fetch';
import { IUploadDataItem } from '../../shared/interfaces';
import { IStore } from '../reducers';

export const NEW_DATA_ARRIVED = 'NEW_DATA_ARRIVED';
export const newDataArrived = (data: IUploadDataItem) => {
    return {
        data,
        type: NEW_DATA_ARRIVED,
    };
};

export const deleteUploadedFile = (dispatch: Dispatch<IStore>, accessKey: string, submitId: number) => {
    return dispatchNetteFetch<{ submitId: number }, any, IStore>(accessKey, dispatch, {
        act: 'revoke',
        data: {
            submitId,
        },
    }, () => null, () => null);
};
