import { coreApp, FyziklaniResultsCoreStore } from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Reducers/coreStore';
import { combineReducers } from 'redux';
import {
    presentation,
    State as PresentationState,
} from './/presentation';

export const app = combineReducers({
    ...coreApp,
    presentation,
});

export interface FyziklaniResultsPresentationStore extends FyziklaniResultsCoreStore {
    presentation: PresentationState;
}
