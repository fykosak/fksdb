import {
    fyziklaniDownloader,
    State as DownloaderState,
} from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/reducer';
import {
    fyziklaniTimer,
    State as TimerState,
} from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/ResultsPresentation/Components/Timer/reducer';
import { fetchReducer, FetchStateMap } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/reducer';
import { fyziklaniData, State as DataState } from './data';

export interface FyziklaniResultsCoreStore {
    data: DataState;
    timer: TimerState;
    downloader: DownloaderState;
    fetch: FetchStateMap;
}

export const coreApp = {
    data: fyziklaniData,
    downloader: fyziklaniDownloader,
    fetch: fetchReducer,
    timer: fyziklaniTimer,
};
