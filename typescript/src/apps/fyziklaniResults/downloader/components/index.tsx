import { NetteActions } from '@appsCollector';
import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { FyziklaniResultsCoreStore } from '../../shared/reducers/coreStore';
import jqXHR = JQuery.jqXHR;
import {
    fetchResults,
    waitForFetch,
} from '../actions';

interface StateProps {
    error: jqXHR<any>;
    isSubmitting: boolean;
    lastUpdated: string;
    refreshDelay: number;
    isRefreshing: boolean;
}

interface DispatchProps {
    onWaitForFetch(lastUpdated: string, delay: number): void;

    onFetch(): void;
}

interface OwnProps {
    accessKey: string;
    actions: NetteActions;
}

class Downloader extends React.Component<DispatchProps & StateProps & OwnProps, {}> {

    public componentDidMount() {
        const {onFetch} = this.props;
        onFetch();
    }

    public componentWillReceiveProps(nextProps: DispatchProps & StateProps & OwnProps) {
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
                <i
                    title={error ? (error.status + ' ' + error.statusText) : lastUpdated}
                    className={isRefreshing ? 'text-success fa fa-check' : 'text-danger fa fa-exclamation-triangle'}/>
                {isSubmitting && (<i className="fa fa-spinner fa-spin"/>)}
                {!isRefreshing && (<button className="btn btn-primary btn-sm" onClick={() => {
                    return onFetch();
                }}>{lang.getText('Fetch')}</button>)}
            </div>
        );
    }
}

const mapStateToProps = (state: FyziklaniResultsCoreStore, ownProps: OwnProps): StateProps => {
    const {accessKey} = ownProps;
    return {
        error: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].error : null,
        isRefreshing: state.downloader.isRefreshing,
        isSubmitting: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].submitting : false,
        lastUpdated: state.downloader.lastUpdated,
        refreshDelay: state.downloader.refreshDelay,
    };
};
/**
 * @throws Error
 */
const mapDispatchToProps = (dispatch: Dispatch<Action<string>>, ownProps: OwnProps): DispatchProps => {
    const {accessKey, actions} = ownProps;
    if (!actions.getAction('refresh')) {
        throw new Error('You need refresh URL');
    }
    const url = actions.getAction('refresh');
    return {
        onFetch: () => fetchResults(accessKey, dispatch, null, url),
        onWaitForFetch: (lastUpdated: string, delay: number): void => waitForFetch(accessKey, dispatch, delay, lastUpdated, url),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(Downloader);
