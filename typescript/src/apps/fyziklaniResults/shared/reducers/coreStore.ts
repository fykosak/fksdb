import { FetchApiState, fetchApi } from '@fetchApi/reducer';
import { fyziklaniDownloader, State as DownloaderState } from '../../downloader/reducer';
import { fyziklaniOptions, State as OptionsState } from '../../hardVisible/reducer';
import { fyziklaniTimer, State as TimerState } from '../../timer/reducer';
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
