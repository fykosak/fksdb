import {
    IUploadDataItem,
} from '../../shared/interfaces';

export const NEW_DATA_ARRIVED = 'NEW_DATA_ARRIVED';
export const newDataArrived = (data: IUploadDataItem) => {
    return {
        data,
        type: NEW_DATA_ARRIVED,
    };
};
