import { ResponseData } from '@apps/fyziklaniResults/downloader/inferfaces';
import { NetteActions } from '@appsCollector/netteActions';
import { dispatchFetch } from '@fetchApi/netteFetch';
import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { FyziklaniResultsCoreStore } from '../shared/reducers/coreStore';

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
    accessKey: string;
    data: ResponseData;
}

class Downloader extends React.Component<DispatchProps & StateProps & OwnProps, {}> {

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
                }}>{lang.getText('Fetch')}</button>)}
            </div>
        );
    }
}

const mapStateToProps = (state: FyziklaniResultsCoreStore, ownProps: OwnProps): StateProps => {
    const {accessKey} = ownProps;
    return {
        actions: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].actions : null,
        error: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].error : null,
        isRefreshing: state.downloader.isRefreshing,
        isSubmitting: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].submitting : false,
        lastUpdated: state.downloader.lastUpdated,
        refreshDelay: state.downloader.refreshDelay,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>, ownProps: OwnProps): DispatchProps => {
    const {accessKey} = ownProps;
    return {
        onFetch: (url) => dispatchFetch<ResponseData>(url, accessKey, dispatch, null),
        onWaitForFetch: (delay: number, url: string): number => setTimeout(() => {
            return dispatchFetch<ResponseData>(url, accessKey, dispatch, null);
        }, delay),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(Downloader);
