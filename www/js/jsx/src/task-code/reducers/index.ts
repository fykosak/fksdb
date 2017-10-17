import { combineReducers } from 'redux';

import {
    IState as INodeState,
    nodes,
} from './nodes';

import {
    IState as ISubmitState,
    submit,
} from './submit';

import { reducer as formReducer } from 'redux-form';

export const app = combineReducers({
    form: formReducer,
    nodes,
    submit,
});

export interface IStore {
    nodes: INodeState;
    submit: ISubmitState;
    form: typeof formReducer;
}
