import { combineReducers } from 'redux';
import { coreApp, FyziklaniResultsCoreStore } from '../../shared/reducers/coreStore';
import {
    State as StatisticsState,
    stats,
} from './stats';

export const app = combineReducers({
    ...coreApp,
    statistics: stats,
});

export interface Store extends FyziklaniResultsCoreStore {
    statistics: StatisticsState;
}
