import {
    fetchApi,
    FetchApiState,
} from '@fetchApi/reducer';
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
