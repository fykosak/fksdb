import { combineReducers } from 'redux';
import { submit } from '../../fetch-api/reducers/submit';
import { provider } from './provider';

export const app = combineReducers({
    provider,
    submit,
});
