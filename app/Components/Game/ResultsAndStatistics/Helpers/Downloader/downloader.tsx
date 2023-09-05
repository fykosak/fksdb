import { Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/submit-model';
import { dispatchNetteFetch } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/nette-fetch';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';
import * as React from 'react';
import { useContext, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import './downloader.scss';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/context';

export default function Downloader() {

    const actions = useSelector((state: Store) => state.fetch.actions);
    const error = useSelector((state: Store) => state.fetch.error);
    const isRefreshing = useSelector((state: Store) => state.downloader.isRefreshing);
    const isSubmitting = useSelector((state: Store) => state.fetch.submitting);
    const lastUpdated = useSelector((state: Store) => state.downloader.lastUpdated);
    const refreshDelay = useSelector((state: Store) => state.downloader.refreshDelay);
    const dispatch = useDispatch();
    useEffect(() => {
        const timerId = window.setTimeout(() => {
            return dispatchNetteFetch<ResponseData>(actions.getAction('refresh'), dispatch, null);
        }, refreshDelay)
        return () => clearTimeout(timerId);
    }, [lastUpdated]);
    const translator = useContext(TranslatorContext);

    return <div className="downloader-update-info bg-white">
        <i
            // @ts-ignore
            title={error ? (error.status + ' ' + error.statusText) : lastUpdated}
            className={isRefreshing ? 'text-success fas fa-check' : 'text-danger fas fa-exclamation-triangle'}/>
        {isSubmitting && <i className="fas fa-spinner fa-spin"/>}
        {!isRefreshing && <button className="btn btn-outline-primary" onClick={() => {
            return dispatchNetteFetch<ResponseData>(actions.getAction('refresh'), dispatch, null)
        }}>{translator.getText('Fetch')}</button>}
    </div>;
}

export interface ResponseData {
    availablePoints: number[];
    basePath: string;
    gameStart: string;
    gameEnd: string;
    times: {
        toStart: number;
        toEnd: number;
        visible: boolean;
    };
    lastUpdated: string;
    isOrganizer: boolean;
    refreshDelay: number;
    tasksOnBoard: number;

    submits: Submits;
    teams?: TeamModel[];
    tasks?: TaskModel[];
    categories?: string[];
}
