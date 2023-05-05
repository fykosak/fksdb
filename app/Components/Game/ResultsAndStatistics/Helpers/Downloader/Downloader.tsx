import { Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/SubmitModel';
import { dispatchNetteFetch } from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/netteFetch';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TaskModel';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';
import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import './downloader.scss';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/LangContext';

interface StateProps {
    error: Response | string | number | Error;
    isSubmitting: boolean;
    lastUpdated: string;
    refreshDelay: number;
    isRefreshing: boolean;
    actions: NetteActions;
}

interface DispatchProps {
    onWaitForFetch(delay: number, url: string): void;

    onFetch(url: string): void;
}

interface OwnProps {
    data: ResponseData;
}

class Downloader extends React.Component<DispatchProps & StateProps & OwnProps> {

    static contextType = TranslatorContext;

    public componentDidUpdate(oldProps: DispatchProps & StateProps & OwnProps) {
        const {lastUpdated: oldLastUpdated} = oldProps;
        if (oldLastUpdated !== this.props.lastUpdated) {

            const {onWaitForFetch, refreshDelay} = this.props;
            if (refreshDelay) {
                const url = this.props.actions.getAction('refresh');
                onWaitForFetch(refreshDelay, url);
            }
        }
    }

    public render() {
        const translator = this.context;
        const {lastUpdated, isRefreshing, isSubmitting, onFetch, error} = this.props;
        return (
            <div className="downloader-update-info bg-white">
                <i
                    // @ts-ignore
                    title={error ? (error.status + ' ' + error.statusText) : lastUpdated}
                    className={isRefreshing ? 'text-success fa fa-check' : 'text-danger fa fa-exclamation-triangle'}/>
                {isSubmitting && (<i className="fa fa-spinner fa-spin"/>)}
                {!isRefreshing && (<button className="btn btn-outline-primary btn-sm" onClick={() => {
                    const url = this.props.actions.getAction('refresh');
                    return onFetch(url);
                }}>{translator.getText('Fetch')}</button>)}
            </div>
        );
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        actions: state.fetch.actions,
        error: state.fetch.error,
        isRefreshing: state.downloader.isRefreshing,
        isSubmitting: state.fetch.submitting,
        lastUpdated: state.downloader.lastUpdated,
        refreshDelay: state.downloader.refreshDelay,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onFetch: (url) => dispatchNetteFetch<ResponseData>(url, dispatch, null),
        onWaitForFetch: (delay: number, url: string): number => window.setTimeout(() => {
            return dispatchNetteFetch<ResponseData>(url, dispatch, null);
        }, delay),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(Downloader);

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
    isOrg: boolean;
    refreshDelay: number;
    tasksOnBoard: number;

    submits: Submits;
    teams?: TeamModel[];
    tasks?: TaskModel[];
    categories?: string[];
}
