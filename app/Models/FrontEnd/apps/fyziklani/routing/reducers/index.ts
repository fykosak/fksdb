import {
    fetchApi,
    FetchApiState,
} from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/reducer';
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

export const app = combineReducers<Store>({
    dragNDrop,
    fetchApi,
    teams,
});

export interface Store {
    teams: RoutingTeamsState;
    dragNDrop: DragNDropState<DragNDropData>;
    fetchApi: FetchApiState;
}
