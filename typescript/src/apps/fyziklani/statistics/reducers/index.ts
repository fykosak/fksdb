import {
    State as FetchApiState,
    submit,
} from '@fetchApi/reducers/submit';
import { combineReducers } from 'redux';
import {
    fyziklaniDownloader,
    State as DownloaderState,
} from '../../downloader/reducers';
import {
    fyziklaniData,
    State as DataState,
} from '../../helpers/reducers/data';
import {
    fyziklaniOptions,
    State as OptionsState,
} from '../../options/reducers/';
import {
    fyziklaniTimer,
    State as TimerState,
} from '../../timer/reducers/timer';
import {
    State as StatisticsState,
    stats,
} from './stats';

export const app = combineReducers({
    data: fyziklaniData,
    downloader: fyziklaniDownloader,
    fetchApi: submit,
    options: fyziklaniOptions,
    statistics: stats,
    timer: fyziklaniTimer,
});

export interface Store {
    data: DataState;
    options: OptionsState;
    downloader: DownloaderState;
    fetchApi: FetchApiState;
    statistics: StatisticsState;
    timer: TimerState;
}
