import { combineReducers } from 'redux';
import {
    dragNDrop,
    IState as IDragNDropState,
} from './dragndrop';

import {
    IState as TSaveState,
    save,
} from './save';
import { teams } from './teams';
import { ITeam } from '../../helpers/interfaces';

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
