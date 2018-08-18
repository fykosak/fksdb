import { combineReducers } from 'redux';
import {
    FormReducer,
    reducer as formReducer,
} from 'redux-form';
import {
    IFetchApiState,
    submit,
} from '../../../fetch-api/reducers/submit';

export const app = combineReducers({
    fetchApi: submit,
    form: formReducer,
});

export interface IFyziklaniSubmitStore {
    fetchApi: IFetchApiState;
    form: FormReducer;
}
