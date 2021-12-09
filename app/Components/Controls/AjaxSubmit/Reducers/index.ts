import { combineReducers } from 'redux';
import {
    dragNDrop,
    State as DragNDropState,
} from './dragNDrop';
import {
    State as UploadDataStore,
    uploadData,
} from './uploadData';

import { fetchReducer, FetchStateMap } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/reducer';
import {
    errorLogger,
    State as ErrorLoggerState,
} from './errorLogger';

export const app = combineReducers<Store>({
    dragNDrop,
    errorLogger,
    fetch: fetchReducer,
    uploadData,
});

export interface Store {
    uploadData: UploadDataStore;
    fetch: FetchStateMap;
    dragNDrop: DragNDropState;
    errorLogger: ErrorLoggerState;
}
