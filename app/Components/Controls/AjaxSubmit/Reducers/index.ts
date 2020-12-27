import { combineReducers } from 'redux';
import {
    dragNDrop,
    State as DragNDropState,
} from './dragNDrop';
import {
    State as UploadDataStore,
    uploadData,
} from './uploadData';

import { fetchApi, FetchApiState } from 'FKSDB/Models/FrontEnd/Fetch/reducer';
import {
    errorLogger,
    State as ErrorLoggerState,
} from './errorLogger';

export const app = combineReducers({
    dragNDrop,
    errorLogger,
    fetchApi,
    uploadData,
});

export interface Store {
    uploadData: UploadDataStore;
    fetchApi: FetchApiState;
    dragNDrop: DragNDropState;
    errorLogger: ErrorLoggerState;
}
