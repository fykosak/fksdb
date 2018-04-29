import {
    IUploadData,
    IUploadDataItem,
} from '../../shared/interfaces';

export const ADD_UPLOAD_SUBMITS = 'ADD_UPLOAD_SUBMITS';
export const addUploadSubmits = (data: IUploadData) => {
    return {
        data,
        type: ADD_UPLOAD_SUBMITS,
    };
};

export const NEW_DATA_ARRIVED = 'NEW_DATA_ARRIVED';
export const newDataArrived = (data: IUploadDataItem) => {
    return {
        data,
        type: NEW_DATA_ARRIVED,
    };
};
