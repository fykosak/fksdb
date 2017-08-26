import { combineReducers } from 'redux';
import {
    timer,
    IState as ITimerState
} from './timer';
import {
    results,
    IState as IResultsState
} from './results';
import {
    options,
    IState as IOptionsState
} from './options';
import {
    downloader,
    IState as IDownloaderState,
} from './downloader';

import {
    tableFilter,
    IState as ITableFilterState
}from './table-filter';
import {
    stats,
    IState as IStatsState
} from './stats';

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
