import {
    inputConnector,
    State as InputConnectorState,
} from '@inputConnector/reducers';
import { combineReducers } from 'redux';

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
