import {timer} from './timer';
import {results} from './results';
import {options} from './options';
import {downloader} from './downloader';
import {combineReducers} from 'redux';
import {tableFilter}from './table-filter';

export const app = combineReducers({
    timer,
    results,
    options,
    downloader,
    tableFilter,
});
