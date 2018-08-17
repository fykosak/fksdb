import { combineReducers } from 'redux';
import {
    IState,
    submit,
} from '../../../fetch-api/reducers/submit';
import {
    fyziklaniData,
    IFyziklaniDataState,
} from '../../helpers/reducers/data';
import {
    fyziklaniDownloader,
    IFyziklaniDownloaderState,
} from '../../helpers/reducers/downloader';
import {
    fyziklaniOptions,
    IFyziklaniOptionsState,
} from '../../helpers/reducers/options';
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
    fetchApi: IState;
    statistics: IFyziklaniStatisticsState;
    timer: IFyziklaniTimerState;
}
