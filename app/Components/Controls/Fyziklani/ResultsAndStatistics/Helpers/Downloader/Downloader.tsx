import { translator } from '@translator/translator';
import { FyziklaniResultsCoreStore } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Reducers/coreStore';
import { Submits } from 'FKSDB/Models/FrontEnd/apps/fyziklani/helpers/interfaces';
import { dispatchFetch } from 'FKSDB/Models/FrontEnd/Fetch/netteFetch';
import { NetteActions } from 'FKSDB/Models/FrontEnd/Loader/netteActions';
import { ModelFyziklaniTask } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTask';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';

interface StateProps {
    error: Error | any;
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

class Downloader extends React.Component<DispatchProps & StateProps & OwnProps, Record<string, never>> {

    public componentDidUpdate(nextProps: DispatchProps & StateProps & OwnProps) {
        const {lastUpdated: oldLastUpdated} = this.props;
        if (oldLastUpdated !== nextProps.lastUpdated) {

            const {onWaitForFetch, refreshDelay} = nextProps;
            if (refreshDelay) {
                const url = this.props.actions.getAction('refresh');
                onWaitForFetch(refreshDelay, url);
            }
        }
    }

    public render() {
        const {lastUpdated, isRefreshing, isSubmitting, onFetch, error} = this.props;
        return (
            <div className="last-update-info bg-white">
                <i
                    title={error ? (error.status + ' ' + error.statusText) : lastUpdated}
                    className={isRefreshing ? 'text-success fa fa-check' : 'text-danger fa fa-exclamation-triangle'}/>
                {isSubmitting && (<i className="fa fa-spinner fa-spin"/>)}
                {!isRefreshing && (<button className="btn btn-primary btn-sm" onClick={() => {
                    const url = this.props.actions.getAction('refresh');
                    return onFetch(url);
                }}>{translator.getText('Fetch')}</button>)}
            </div>
        );
    }
}

const mapStateToProps = (state: FyziklaniResultsCoreStore): StateProps => {
    return {
        actions: state.fetchApi.actions,
        error: state.fetchApi.error,
        isRefreshing: state.downloader.isRefreshing,
        isSubmitting: state.fetchApi.submitting,
        lastUpdated: state.downloader.lastUpdated,
        refreshDelay: state.downloader.refreshDelay,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onFetch: (url) => dispatchFetch<ResponseData>(url, dispatch, null),
        onWaitForFetch: (delay: number, url: string): number => window.setTimeout(() => {
            return dispatchFetch<ResponseData>(url, dispatch, null);
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
    teams?: ModelFyziklaniTeam[];
    tasks?: ModelFyziklaniTask[];
    categories?: string[];
}
