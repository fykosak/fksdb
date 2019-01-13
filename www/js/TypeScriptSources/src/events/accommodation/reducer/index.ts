import { combineReducers } from 'redux';
import {
    inputConnector,
    State as InputConnectorState,
} from '../../../input-connector/reducers';

export const app = combineReducers({
    inputConnector,
});

export interface Store {
    inputConnector: InputConnectorState;
}
