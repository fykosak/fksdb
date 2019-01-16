import { combineReducers } from 'redux';
import {
    State as FetchApiState,
    submit,
} from '../../../fetch-api/reducers/submit';
import {
    fyziklaniDownloader,
    State as DownloaderState,
} from '../../helpers/downloader/reducers';
import {
    fyziklaniOptions,
    State as OptionsState,
} from '../../helpers/options/reducers/';
import {
    fyziklaniData,
    State as DataState,
} from '../../helpers/reducers/data';
import {
    fyziklaniTimer,
    State as TimerState,
} from '../../helpers/reducers/timer';
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
