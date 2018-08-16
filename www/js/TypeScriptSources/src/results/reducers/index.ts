import { combineReducers } from 'redux';

import {
    downloader,
    IState as IDownloaderState,
} from '../../fyziklani/helpers/reducers/downloader';
import {
    IState as IOptionsState,
    options,
} from '../../fyziklani/helpers/reducers/options';

import {
    IState as IResultsState,
    results,
} from '../../fyziklani/helpers/reducers/results';

import {
    IState as IStatsState,
    stats,
} from './stats';

import {
    IState as ITableFilterState,
    tableFilter,
} from '../../fyziklani/results/reducers/table-filter';

import {
    IState as ITimerState,
    timer,
} from '../../fyziklani/results/reducers/timer';

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
