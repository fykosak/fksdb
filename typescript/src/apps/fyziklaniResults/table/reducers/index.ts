import { combineReducers } from 'redux';
import { coreApp, FyziklaniResultsCoreStore } from '../../shared/reducers/coreStore';
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
