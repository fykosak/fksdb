import { coreApp, FyziklaniCoreStore } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Reducers/coreStore';
import { combineReducers } from 'redux';
import {
    fyziklaniTableFilter,
    State as TableFilterState,
} from './tableFilter';

export const app = combineReducers({
    ...coreApp,
    tableFilter: fyziklaniTableFilter,
});

export interface FyziklaniStatisticsTableStore extends FyziklaniCoreStore {
    tableFilter: TableFilterState;
}
