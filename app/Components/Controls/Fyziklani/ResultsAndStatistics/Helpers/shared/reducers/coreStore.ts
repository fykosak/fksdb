import { FetchApiState, fetchApi } from '@fetchApi/reducer';
import { fyziklaniDownloader, State as DownloaderState } from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Reducer';
import { fyziklaniOptions, State as OptionsState } from '../../HardVisible/reducer';
import { fyziklaniTimer, State as TimerState } from '../../Timer/reducer';
import { fyziklaniData, State as DataState } from './data';

export interface FyziklaniResultsCoreStore {
    data: DataState;
    timer: TimerState;
    options: OptionsState;
    downloader: DownloaderState;
    fetchApi: FetchApiState;
}

export const coreApp = {
    data: fyziklaniData,
    downloader: fyziklaniDownloader,
    fetchApi,
    options: fyziklaniOptions,
    timer: fyziklaniTimer,
};
