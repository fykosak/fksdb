import { combineReducers } from 'redux';
import {
    dragNDrop,
    State as DragNDropState,
} from './DragNDrop';
import {
    State as UploadDataStore,
    uploadData,
} from './UploadData';

import {
    fetchApi,
    FetchApiState,
} from '@fetchApi/reducer';
import {
    errorLogger,
    State as ErrorLoggerState,
} from './ErrorLogger';

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
