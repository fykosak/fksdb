import { State as FetchApiState, fetchApi } from '@fetchApi/reducers';
import { fyziklaniDownloader, State as DownloaderState } from '../../downloader/reducers';
import { fyziklaniOptions, State as OptionsState } from '../../hardVisible/reducers';
import { fyziklaniTimer, State as TimerState } from '../../timer/reducers';
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
