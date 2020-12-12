import { combineReducers } from 'redux';
import { coreApp, FyziklaniResultsCoreStore } from '../../Helpers/shared/reducers/coreStore';
import {
    presentation,
    State as PresentationState,
} from '../reducers/presentation';

export const app = combineReducers({
    ...coreApp,
    presentation,
});

export interface FyziklaniResultsPresentationStore extends FyziklaniResultsCoreStore {
    presentation: PresentationState;
}
