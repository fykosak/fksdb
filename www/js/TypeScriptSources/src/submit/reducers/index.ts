import { combineReducers } from 'redux';
import {
    dragNDrop,
    IState as IDragNDropState,
} from '../../shared/reducers/dragndrop';
import {
    IState as IUploadDataStore,
    uploadData,
} from './upload-data';

import {
    fetchApi,
    IFetchApiState,
} from '../../fetch-api/reducers/';

import {
    errorLogger,
    IState as IErrorLoggerState,
} from '../../shared/reducers/error-logger';

export const app = combineReducers({
    dragNDrop,
    errorLogger,
    fetchApi,
    uploadData,
});

export interface IStore {
    uploadData: IUploadDataStore;
    fetchApi: IFetchApiState;
    dragNDrop: IDragNDropState;
    errorLogger: IErrorLoggerState;
}
