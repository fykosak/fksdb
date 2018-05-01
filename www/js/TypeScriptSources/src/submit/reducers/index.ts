import { combineReducers } from 'redux';
import {
    IState as IUploadDataStore,
    uploadData,
} from './upload-data';

import {
    IState as ISubmitState,
    submit,
} from '../../shared/reducers/submit';

export const app = combineReducers({
    submit,
    uploadData,
});

export interface IStore {
    uploadData: IUploadDataStore;
    submit: ISubmitState;
}
