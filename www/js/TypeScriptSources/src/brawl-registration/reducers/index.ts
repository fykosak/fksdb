import { combineReducers } from 'redux';

import { reducer as formReducer } from 'redux-form';
import {
    definitions,
    IDefinitionsState,
} from './definitions';
import { provider } from './provider';

export const app = combineReducers({
    definitions,
    form: formReducer,
    provider,
});

export interface IStore {
    definitions: IDefinitionsState;
    form: typeof formReducer;
}
