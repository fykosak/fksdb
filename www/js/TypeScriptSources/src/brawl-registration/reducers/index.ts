import { combineReducers } from 'redux';

import { reducer as formReducer } from 'redux-form';

export const app = combineReducers({
    form: formReducer,
});

export interface IStore {
    form: typeof formReducer;
}
