import {
    downloader,
    State as DownloaderState,
} from './downloader';
import {
    table,
    State as TableState,
} from './table';
import {
    timer,
    State as TimerState,
} from './timer';
import {
    fetchReducer,
    FetchStateMap,
} from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/reducer';
import {
    data,
    State as DataState,
} from './data';
import {
    stats,
    State as StatState,
} from './stats';
import {
    presentation,
    State as PresentationState,
} from './presentation';
import { combineReducers } from 'redux';

export interface Store {
    data: DataState;
    timer: TimerState;
    downloader: DownloaderState;
    fetch: FetchStateMap;
    presentation: PresentationState;
    tableFilter: TableState;
    statistics: StatState;
}

export const app = combineReducers<Store>({
    data,
    downloader,
    fetch: fetchReducer,
    timer,
    presentation,
    tableFilter: table,
    statistics: stats,

});
