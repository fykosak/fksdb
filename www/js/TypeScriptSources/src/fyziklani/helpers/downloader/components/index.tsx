import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { INetteActions } from '../../../../app-collector/';
import { lang } from '../../../../i18n/i18n';
import { FyziklaniResultsStore } from '../../../results/reducers/';
import {
    fetchResults,
    waitForFetch,
} from '../actions/';
import jqXHR = JQuery.jqXHR;

interface IState {
    error?: jqXHR<any>;
    isSubmitting?: boolean;
    lastUpdated?: string;
    refreshDelay?: number;
    isRefreshing?: boolean;

    onWaitForFetch?(lastUpdated: string, delay: number): void;

    onFetch?(): void;
}

interface IProps {
    accessKey: string;
    actions: INetteActions;
}

class Downloader extends React.Component<IState & IProps, {}> {

    public componentDidMount() {
        const {onFetch} = this.props;
        onFetch();
    }

    public componentWillReceiveProps(nextProps: IState & IProps) {
        const {lastUpdated: oldLastUpdated} = this.props;
        if (oldLastUpdated !== nextProps.lastUpdated) {

            const {onWaitForFetch, refreshDelay, lastUpdated} = nextProps;
            if (refreshDelay) {
                onWaitForFetch(lastUpdated, refreshDelay);
            }
        }
    }

    public render() {
        const {lastUpdated, isRefreshing, isSubmitting, onFetch, error} = this.props;
        return (
            <div className="last-update-info bg-white">
                <span
                className={isRefreshing ? 'text-success' : 'text-danger'}>
                {lastUpdated}
                </span>
                {isSubmitting && (<i className="fa fa-spinner fa-spin"/>)}
                {!isRefreshing && (<button className="btn btn-primary btn-sm" onClick={() => {
                    return onFetch();
                }}>{lang.getText('Fetch')}</button>)}
                {error && <span className={'text-danger'}>{error.status} {error.statusText}</span>}
            </div>
        );
    }
}

const mapStateToProps = (state: FyziklaniResultsStore, ownProps: IProps): IState => {
    const {accessKey} = ownProps;
    return {
        error: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].error : null,
        isRefreshing: state.downloader.isRefreshing,
        isSubmitting: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].submitting : false,
        lastUpdated: state.downloader.lastUpdated,
        refreshDelay: state.downloader.refreshDelay,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>, ownProps: IProps): IState => {
    const {accessKey, actions} = ownProps;
    if (!actions.hasOwnProperty('refresh')) {
        throw new Error('you need to have refresh URL');
    }
    const url = actions.refresh;
    return {
        onFetch: () => fetchResults(accessKey, dispatch, null, url),
        onWaitForFetch: (lastUpdated: string, delay: number): void => waitForFetch(accessKey, dispatch, delay, lastUpdated, url),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(Downloader);
