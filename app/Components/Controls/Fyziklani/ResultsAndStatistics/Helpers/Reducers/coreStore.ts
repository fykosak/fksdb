import {
    downloader,
    State as DownloaderState,
} from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/reducer';
import {
    timer,
    State as TimerState,
} from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/ResultsPresentation/Components/Timer/reducer';
import { fetchReducer, FetchStateMap } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/reducer';
import { data, State as DataState } from './data';

export interface CoreStore {
    data: DataState;
    timer: TimerState;
    downloader: DownloaderState;
    fetch: FetchStateMap;
}

export const coreApp = {
    data,
   downloader,
    fetch: fetchReducer,
    timer,
};
