import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { INetteActions } from '../../../../app-collector/';
import { lang } from '../../../../i18n/i18n';
import { IFyziklaniResultsStore } from '../../../results/reducers/';
import {
    fetchResults,
    waitForFetch,
} from '../actions/';

interface IState {
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
        const {lastUpdated, isRefreshing, onFetch} = this.props;
        return (
            <div className="last-update-info">{lang.getText('lastUpdated')}: <span
                className={isRefreshing ? 'text-success' : 'text-danger'}>
                {lastUpdated}
                </span>
                {!isRefreshing && (<button className="btn btn-primary" onClick={() => {
                    return onFetch();
                }}>{lang.getText('Fetch')}</button>)}
            </div>
        );
    }
}

const mapStateToProps = (state: IFyziklaniResultsStore, ownProps: IProps): IState => {
    const {accessKey} = ownProps;
    return {
        isRefreshing: state.downloader.isRefreshing,
        isSubmitting: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].submitting : false,
        lastUpdated: state.downloader.lastUpdated,
        refreshDelay: state.downloader.refreshDelay,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniResultsStore>, ownProps: IProps): IState => {
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
