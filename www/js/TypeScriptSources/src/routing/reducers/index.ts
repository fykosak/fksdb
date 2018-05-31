import { combineReducers } from 'redux';
import { ITeam } from '../../shared/interfaces';
import {
    dragNDrop,
    IState as IDragNDropState,
} from './dragndrop';

import {
    IState as TSaveState,
    save,
} from './save';
import { teams } from './teams';

import {
    IState as ISubmitState,
    submit,
} from '../../fetch-api/reducers/submit';

export const app = combineReducers({
    dragNDrop,
    save,
    submit,
    teams,
});

export interface IStore {
    teams: ITeam[];
    dragNDrop: IDragNDropState;
    save: TSaveState;
    submit: ISubmitState;
}
