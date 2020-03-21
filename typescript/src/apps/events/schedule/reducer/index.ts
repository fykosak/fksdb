import {
    inputConnector,
    State as InputConnectorState,
} from '@inputConnector/reducers';
import { combineReducers } from 'redux';

export const app = combineReducers({
    inputConnector,
});

export interface Store {
    inputConnector: InputConnectorState;
}
