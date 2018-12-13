import { combineReducers } from 'redux';
import {
    IInputConnectorState,
    inputConnector,
} from '../../../input-connector/reducers';

export const app = combineReducers({
    inputConnector,
});

export interface IPaymentSelectStore {
    inputConnector: IInputConnectorState;
}
