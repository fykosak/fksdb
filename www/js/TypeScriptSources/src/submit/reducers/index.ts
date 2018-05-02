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

export const app = combineReducers({
    dragNDrop,
    submit,
    uploadData,
});

export interface IStore {
    uploadData: IUploadDataStore;
    submit: ISubmitState;
    dragNDrop: IDragNDropState;
}
