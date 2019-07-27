import { combineReducers } from 'redux';
import {
    inputConnector,
    State as InputConnectorState,
} from '../../../input-connector/reducers';

import {
    compactValue,
    State as CompactValueState,
} from './compactValue';

export const app = combineReducers({
    compactValue,
    inputConnector,
});

export interface Store {
    inputConnector: InputConnectorState;
    compactValue: CompactValueState;
}
