import {
    inputConnectorReducer,
    InputConnectorStateMap,
} from 'vendor/fykosak/nette-frontend-component/src/InputConnector/reducer';
import { combineReducers } from 'redux';

export const app = combineReducers({
    inputConnector: inputConnectorReducer,
});

export interface Store {
    inputConnector: InputConnectorStateMap;
}
