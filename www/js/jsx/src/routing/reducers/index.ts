import { combineReducers } from 'redux';
import { ITeam } from '../interfaces';
import {
    dragNDrop,
    IState as IDragNDropState,
} from './dragndrop';

import {
    IState as TSaveState,
    save,
} from './save';
import { teams } from './teams';

export const app = combineReducers({
    dragNDrop,
    save,
    teams,
});

export interface IStore {
    teams: ITeam[];
    dragNDrop: IDragNDropState;
    save: TSaveState;
}
