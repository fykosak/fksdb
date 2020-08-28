import {
    fetchApi,
    FetchApiState,
} from '@fetchApi/reducer';
import { combineReducers } from 'redux';
import {
    FormReducer,
    reducer as formReducer,
} from 'redux-form';

export const app = combineReducers({
    fetchApi,
    form: formReducer,
});

export interface Store {
    fetchApi: FetchApiState;
    form: FormReducer;
}
