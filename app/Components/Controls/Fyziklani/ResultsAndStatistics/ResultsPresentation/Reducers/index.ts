import { coreApp, FyziklaniCoreStore } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Reducers/coreStore';
import { combineReducers } from 'redux';
import {
    presentation,
    State as PresentationState,
} from './presentation';

export const app = combineReducers<FyziklaniPresentationStore>({
    ...coreApp,
    presentation,
});

export interface FyziklaniPresentationStore extends FyziklaniCoreStore {
    presentation: PresentationState;
}
