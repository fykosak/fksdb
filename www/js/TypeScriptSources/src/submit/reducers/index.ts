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
    IState as ISubmitState,
    submit,
} from '../../shared/reducers/submit';

import {
    errorLogger,
    IState as IErrorLoggerState,
} from '../../shared/reducers/error-logger';

export const app = combineReducers({
    dragNDrop,
    errorLogger,
    submit,
    uploadData,
});

export interface IStore {
    uploadData: IUploadDataStore;
    submit: ISubmitState;
    dragNDrop: IDragNDropState;
    errorLogger: IErrorLoggerState;
}
