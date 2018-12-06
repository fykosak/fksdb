import { combineReducers } from 'redux';
import {
    IInputConnectorState,
    inputConnector,
} from '../../../input-connector/reducers';

import {
    compactValue,
    ICompactValueState,
} from './compart-value';

export const app = combineReducers({
    compactValue,
    inputConnector,
});

export interface IFyziklaniScheduleStore {
    inputConnector: IInputConnectorState;
    compactValue: ICompactValueState;
}
