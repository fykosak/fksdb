import {
    State as FetchApiState,
    submit,
} from '@fetchApi/reducers/submit';
import { combineReducers } from 'redux';
import {
    FormReducer,
    reducer as formReducer,
} from 'redux-form';

export const app = combineReducers({
    fetchApi: submit,
    form: formReducer,
});

export interface Store {
    fetchApi: FetchApiState;
    form: FormReducer;
}
