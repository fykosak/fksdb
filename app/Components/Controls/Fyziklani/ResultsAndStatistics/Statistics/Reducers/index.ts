import { coreApp, FyziklaniCoreStore } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Reducers/coreStore';
import { combineReducers } from 'redux';
import {
    State as StatisticsState,
    stats,
} from './stats';

export const app = combineReducers({
    ...coreApp,
    statistics: stats,
});

export interface FyziklaniStatisticStore extends FyziklaniCoreStore {
    statistics: StatisticsState;
}
