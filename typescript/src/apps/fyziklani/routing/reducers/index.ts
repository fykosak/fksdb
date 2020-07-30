import {
    State as FetchApiState,
    fetchApi,
} from '@fetchApi/reducers';
import { combineReducers } from 'redux';
import { DragNDropData } from '../middleware/interfaces';
import {
    dragNDrop,
    State as DragNDropState,
} from './dragndrop';
import {
    State as RoutingTeamsState,
    teams,
} from './teams';

export const app = combineReducers({
    dragNDrop,
    fetchApi,
    teams,

});

export interface Store {
    teams: RoutingTeamsState;
    dragNDrop: DragNDropState<DragNDropData>;
    fetchApi: FetchApiState;
}
