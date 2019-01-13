import { combineReducers } from 'redux';
import {
    State as FetchApiState,
    submit,
} from '../../../fetch-api/reducers/submit';
import {
    fyziklaniDownloader,
    State as DownloaderState,
} from '../../helpers/downloader/reducers/';
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
    presentation,
    State as PresentationState,
} from './presentation';
import {
    fyziklaniTableFilter,
    State as TableFilterState,
} from './table-filter';

export const app = combineReducers({
    data: fyziklaniData,
    downloader: fyziklaniDownloader,
    fetchApi: submit,
    options: fyziklaniOptions,
    presentation,
    tableFilter: fyziklaniTableFilter,
    timer: fyziklaniTimer,
});

export interface FyziklaniResultsStore {
    data: DataState;
    tableFilter: TableFilterState;
    timer: TimerState;
    options: OptionsState;
    downloader: DownloaderState;
    fetchApi: FetchApiState;
    presentation: PresentationState;
}
