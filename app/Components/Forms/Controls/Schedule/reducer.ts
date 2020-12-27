import {
    inputConnector,
    State as InputConnectorState,
} from 'FKSDB/Models/FrontEnd/InputConnector/reducer';
import { combineReducers } from 'redux';

export const app = combineReducers({
    inputConnector,
});

export interface Store {
    inputConnector: InputConnectorState;
}
