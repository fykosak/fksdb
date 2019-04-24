import { combineReducers } from 'redux';
import {
    FormReducer,
    reducer as formReducer,
} from 'redux-form';
import {
    State as FetchApiState,
    submit,
} from '../../../fetch-api/reducers/submit';

export const app = combineReducers({
    fetchApi: submit,
    form: formReducer,
});

export interface Store {
    fetchApi: FetchApiState;
    form: FormReducer;
}
