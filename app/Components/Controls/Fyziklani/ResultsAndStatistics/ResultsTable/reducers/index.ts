import { coreApp, FyziklaniResultsCoreStore } from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Reducers/coreStore';
import { combineReducers } from 'redux';
import {
    fyziklaniTableFilter,
    State as TableFilterState,
} from './tableFilter';

export const app = combineReducers({
    ...coreApp,
    tableFilter: fyziklaniTableFilter,
});

export interface FyziklaniResultsTableStore extends FyziklaniResultsCoreStore {
    tableFilter: TableFilterState;
}
