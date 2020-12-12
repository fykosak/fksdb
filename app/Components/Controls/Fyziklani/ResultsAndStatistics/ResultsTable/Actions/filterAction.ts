import { Action } from 'redux';
import { Filter } from '../Filter';

export interface FilterAction extends Action<string> {
    filter: Filter;
}
