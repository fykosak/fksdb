import { Action } from 'redux';
import { Filter } from '../middleware/filters/filter';

export interface FilterAction extends Action<string> {
    filter: Filter;
}
