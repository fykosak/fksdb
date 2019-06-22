import { combineReducers } from 'redux';
import {
    dragNDrop,
    IState as DragNDropState,
} from './dragNDrop';
import {
    IState as UploadDataStore,
    uploadData,
} from './uploadData';

import {
    State as FetchApiState,
    submit,
} from '../../fetch-api/reducers/submit';
import {
    errorLogger,
    State as ErrorLoggerState,
} from './errorLogger';

export const app = combineReducers({
    dragNDrop,
    errorLogger,
    fetchApi: submit,
    uploadData,
});

export interface Store {
    uploadData: UploadDataStore;
    fetchApi: FetchApiState;
    dragNDrop: DragNDropState;
    errorLogger: ErrorLoggerState;
}
