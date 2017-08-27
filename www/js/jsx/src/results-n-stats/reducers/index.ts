import { combineReducers } from 'redux';

import {
    downloader,
    IState as IDownloaderState,
} from './downloader';
import {
    IState as IOptionsState,
    options,
} from './options';

import {
    IState as IResultsState,
    results,
} from './results';

import {
    IState as IStatsState,
    stats,
} from './stats';

import {
    IState as ITableFilterState,
    tableFilter,
} from './table-filter';

import {
    IState as ITimerState,
    timer,
} from './timer';

export const app = combineReducers({
    downloader,
    options,
    results,
    stats,
    tableFilter,
    timer,
});

export interface IStore {
    downloader: IDownloaderState;
    timer: ITimerState;
    results: IResultsState;
    options: IOptionsState;
    tableFilter: ITableFilterState;
    stats: IStatsState;
}
