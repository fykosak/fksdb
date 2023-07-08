import { combineReducers } from 'redux';
import {
    dragNDrop,
    State as DragNDropState,
} from './dragNDrop';
import {
    State as UploadDataStore,
    upload,
} from './upload';

import { fetchReducer, FetchStateMap } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/reducer';
import {
    errors,
    State as ErrorLoggerState,
} from './errors';

export const app = combineReducers<Store>({
    dragNDrop,
    errorLogger: errors,
    fetch: fetchReducer,
    uploadData: upload,
});

export interface Store {
    uploadData: UploadDataStore;
    fetch: FetchStateMap;
    dragNDrop: DragNDropState;
    errorLogger: ErrorLoggerState;
}
