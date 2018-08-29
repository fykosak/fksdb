import { combineReducers } from 'redux';
import {
    IFetchApiState,
    submit,
} from '../../../fetch-api/reducers/submit';
import {
    fyziklaniDownloader,
    IFyziklaniDownloaderState,
} from '../../helpers/downloader/reducers';
import {
    fyziklaniOptions,
    IFyziklaniOptionsState,
} from '../../helpers/options/reducers/';
import {
    fyziklaniData,
    IFyziklaniDataState,
} from '../../helpers/reducers/data';
import {
    fyziklaniTimer,
    IFyziklaniTimerState,
} from '../../helpers/reducers/timer';
import {
    IFyziklaniStatisticsState,
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

export interface IFyziklaniStatisticsStore {
    data: IFyziklaniDataState;
    options: IFyziklaniOptionsState;
    downloader: IFyziklaniDownloaderState;
    fetchApi: IFetchApiState;
    statistics: IFyziklaniStatisticsState;
    timer: IFyziklaniTimerState;
}
