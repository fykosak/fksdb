import { combineReducers } from 'redux';
import {
    IFetchApiState,
    submit,
} from '../../../fetch-api/reducers/submit';
import { IRoutingDragNDropData } from '../middleware/interfaces';
import {
    dragNDrop,
    IState as IDragNDropState,
} from './dragndrop';
import {
    IFyziklaniRoutingTeamsState,
    teams,
} from './teams';

export const app = combineReducers({
    dragNDrop,
    fetchApi: submit,
    teams,

});

export interface IFyziklaniRoutingStore {
    teams: IFyziklaniRoutingTeamsState;
    dragNDrop: IDragNDropState<IRoutingDragNDropData>;
    fetchApi: IFetchApiState;
}
