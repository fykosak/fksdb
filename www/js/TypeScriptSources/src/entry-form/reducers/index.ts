import { combineReducers } from 'redux';

import {
    IState as ISubmitState,
    submit,
} from './submit';

import { reducer as formReducer } from 'redux-form';

export const app = combineReducers({
    form: formReducer,
    submit,
});

export interface IStore {
    submit: ISubmitState;
    form: typeof formReducer;
}
