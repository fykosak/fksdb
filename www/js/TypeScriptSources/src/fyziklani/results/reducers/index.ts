import { combineReducers } from 'redux';
import {
    fyziklaniData,
    IFyziklaniDataState,
} from '../../helpers/reducers/data';
import {
    fyziklaniOptions,
    IFyziklaniOptionsState,
} from '../../helpers/reducers/options';
import {
    fyziklaniTableFilter,
    IFyziklaniTableFilterState,
} from './table-filter';
import {
    fyziklaniTimer,
    IFyziklaniTimerState,
} from './timer';
import {
    fyziklaniDownloader,
    IFyziklaniDownloaderState,
} from '../../helpers/reducers/downloader';
import {
    IState,
    submit,
} from '../../../fetch-api/reducers/submit';

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
    fetchApi: IState;

}
