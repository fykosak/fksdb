import { combineReducers } from 'redux';
import {
    IFetchApiState,
    submit,
} from '../../../fetch-api/reducers/submit';
import {
    fyziklaniDownloader,
    IFyziklaniDownloaderState,
} from '../../helpers/downloader/reducers/';
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
    fyziklaniTableFilter,
    IFyziklaniTableFilterState,
} from './table-filter';

export const app = combineReducers({
    data: fyziklaniData,
    downloader: fyziklaniDownloader,
    fetchApi: submit,
    options: fyziklaniOptions,
    tableFilter: fyziklaniTableFilter,
    timer: fyziklaniTimer,
});

export interface IFyziklaniResultsStore {
    data: IFyziklaniDataState;
    tableFilter: IFyziklaniTableFilterState;
    timer: IFyziklaniTimerState;
    options: IFyziklaniOptionsState;
    downloader: IFyziklaniDownloaderState;
    fetchApi: IFetchApiState;
}
