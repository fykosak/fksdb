import {
    inputConnector,
    State as InputConnectorState,
} from '@inputConnector/reducer';
import { combineReducers } from 'redux';

export const app = combineReducers({
    inputConnector,
});

export interface Store {
    inputConnector: InputConnectorState;
}
