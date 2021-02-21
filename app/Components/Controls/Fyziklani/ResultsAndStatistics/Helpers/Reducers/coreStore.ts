import { fyziklaniDownloader, State as DownloaderState } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/reducer';
import { fyziklaniOptions, State as OptionsState } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/HardVisible/reducer';
import { fyziklaniTimer, State as TimerState } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Timer/reducer';
import { fetchApi, FetchApiState } from 'FKSDB/Models/FrontEnd/Fetch/reducer';
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
