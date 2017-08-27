import { combineReducers } from 'redux';
import { ITeam } from '../interfaces';
import {
    dragNDrop,
    IState as IDragNDropState,
} from './dragndrop';
import { teams } from './teams';

export const app = combineReducers({
    dragNDrop,
    teams,
});

export interface IStore {
    teams: ITeam[];
    dragNDrop: IDragNDropState;
}
