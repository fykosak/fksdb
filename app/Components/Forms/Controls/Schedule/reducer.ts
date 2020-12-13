import {
    inputConnector,
    State as InputConnectorState,
} from '@FKSDB/Model/FrontEnd/InputConnector/reducer';
import { combineReducers } from 'redux';

export const app = combineReducers({
    inputConnector,
});

export interface Store {
    inputConnector: InputConnectorState;
}
