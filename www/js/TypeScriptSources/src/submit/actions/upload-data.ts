import { Dispatch } from 'redux';
import {
    submitFail,
    submitStart,
    submitSuccess,
} from '../../shared/actions/submit';
import { netteFetch } from '../../shared/helpers/fetch';
import {
    IReciveData,
    IUploadDataItem,
} from '../../shared/interfaces';
import { IStore } from '../reducers';

export const NEW_DATA_ARRIVED = 'NEW_DATA_ARRIVED';
export const newDataArrived = (data: IUploadDataItem) => {
    return {
        data,
        type: NEW_DATA_ARRIVED,
    };
};

export const deleteUploadedFile = (dispatch: Dispatch<IStore>, submitId: number) => {
    dispatch(submitStart());
    return netteFetch({
            act: 'revoke',
            submitId,
        },
        (data: IReciveData<any>) => dispatch(submitSuccess(data)),
        (e) => dispatch(submitFail(e)));
};
