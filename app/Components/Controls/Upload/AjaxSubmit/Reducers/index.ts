import { combineReducers } from 'redux';
import { dragDrop } from './drag-drop';
import { State as UploadDataStore, upload } from './upload';
import { fetchReducer, FetchStateMap } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/reducer';
import { errors, State as ErrorLoggerState } from './errors';

export const app = combineReducers<Store>({
    dragNDrop: dragDrop,
    errorLogger: errors,
    fetch: fetchReducer,
    uploadData: upload,
});

export interface Store {
    uploadData: UploadDataStore;
    fetch: FetchStateMap;
    dragNDrop: boolean;
    errorLogger: ErrorLoggerState;
}
