import { coreApp, CoreStore } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Reducers/coreStore';
import { combineReducers } from 'redux';
import {
    tableFilter,
    State as TableFilterState,
} from './tableFilter';

export const app = combineReducers({
    ...coreApp,
    tableFilter,
});

export interface StatisticsTableStore extends CoreStore {
    tableFilter: TableFilterState;
}
