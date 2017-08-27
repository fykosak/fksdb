import { combineReducers } from 'redux';
import { dragNDrop } from './dragndrop';
import { teams } from './teams';

export const app = combineReducers({
    dragNDrop,
    teams,
});
