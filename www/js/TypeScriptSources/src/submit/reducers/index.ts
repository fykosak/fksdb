import { combineReducers } from 'redux';
import { uploadData } from './upload-data';

export const app = combineReducers({
    uploadData,
});
