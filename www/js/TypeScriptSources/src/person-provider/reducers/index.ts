import { combineReducers } from 'redux';

import {
    IState as ISubmitStore,
    submit,
} from '../../shared/reducers/submit';

import {
    IProviderStore,
    provider,
} from './provider';

export const app = combineReducers({
    provider,
    submit,
});

export interface IStore {
    submit: ISubmitStore;
    provider: IProviderStore;
}
