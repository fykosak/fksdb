import { combineReducers } from 'redux';
import {
    accommodation,
    IAccommodationState,
} from './accommodation';

export const app = combineReducers({
    accommodation,
});

export interface IStore {
    accommodation: IAccommodationState;
}
