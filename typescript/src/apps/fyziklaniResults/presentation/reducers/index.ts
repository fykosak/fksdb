import { combineReducers } from 'redux';
import {
    presentation,
    State as PresentationState,
} from '../../presentation/reducers/presentation';
import { coreApp, FyziklaniResultsCoreStore } from '../../shared/reducers/coreStore';

export const app = combineReducers({
    ...coreApp,
    presentation,
});

export interface FyziklaniResultsPresentationStore extends FyziklaniResultsCoreStore {
    presentation: PresentationState;
}
