import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { lang } from '../../../../i18n/i18n';
import { IFyziklaniResultsStore } from '../../../results/reducers';
import {
    fetchResults,
    waitForFetch,
} from './fetch';

interface IState {
    isSubmitting?: boolean;
    lastUpdated?: string;
    refreshDelay?: number;
    isRefreshing?: boolean;
    onFetch?: () => void;
    onWaitForFetch?: (lastUpdated: string, delay: number) => any;
}

interface IProps {
    accessKey: string;
}

class Downloader extends React.Component<IState & IProps, {}> {
    public componentDidMount() {
        const {onFetch} = this.props;
        onFetch();
    }

    public componentWillReceiveProps(nextProps: IState & IProps) {
        if (this.props.lastUpdated !== nextProps.lastUpdated) {
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
    // const accessKey = '@@fyziklani-results/';
    const {accessKey} = ownProps;
    return {
        onFetch: () => fetchResults(accessKey, dispatch, null),
        onWaitForFetch: (lastUpdated: string, delay: number): any => waitForFetch(accessKey, dispatch, delay, lastUpdated),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(Downloader);
