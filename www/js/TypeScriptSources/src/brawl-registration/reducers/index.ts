import { combineReducers } from 'redux';

import { reducer as formReducer } from 'redux-form';
import { provider } from './provider';

export const app = combineReducers({
    form: formReducer,
    provider,
});

export interface IStore {
    form: typeof formReducer;
}
