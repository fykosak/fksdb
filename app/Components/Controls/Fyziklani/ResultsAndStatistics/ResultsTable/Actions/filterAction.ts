import { Action } from 'redux';
import { Filter } from '../filter';

export interface FilterAction extends Action<string> {
    filter: Filter;
}
