import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import {
    fetchResults,
    waitForFetch,
} from '../../helpers/fetch';
import { IStore } from '../../reducers';
import Lang from '../../../lang/components/lang';

interface IState {
    lastUpdated?: string;
    refreshDelay?: number;
    isRefreshing?: boolean;
    onFetch?: () => void;
    onWaitForFetch?: (lastUpdated: string, delay: number) => any;
}

class Downloader extends React.Component<IState, {}> {
    public componentDidMount() {
        const {onFetch} = this.props;
        onFetch();
    }

    public componentWillReceiveProps(nextProps) {
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
            <div className="last-update-info"><Lang text={'lastUpdated'}/>: <span
                className={isRefreshing ? 'text-success' : 'text-danger'}>
                {lastUpdated}
                </span>
                {!isRefreshing && (<button className="btn btn-primary" onClick={() => {
                    return onFetch();
                }}>Fetch</button>)}
            </div>
        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        isRefreshing: state.downloader.isRefreshing,
        lastUpdated: state.downloader.lastUpdated,
        refreshDelay: state.downloader.refreshDelay,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onFetch: () => fetchResults(dispatch, null),
        onWaitForFetch: (lastUpdated: string, delay: number): any => waitForFetch(dispatch, delay, lastUpdated),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(Downloader);
