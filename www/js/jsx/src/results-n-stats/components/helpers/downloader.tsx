import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import {
    fetchResults,
    waitForFetch,
} from '../../helpers/fetch';
import { IStore } from '../../reducers/index';

interface IState {
    lastUpdated?: string;
    refreshDelay?: number;
    onFetch?: () => void;
    onWaitForFetch?: (lastUpdated: string, delay: number) => any;
}

class Downloader extends React.Component<IState, {}> {
    public componentDidMount() {
        const { onFetch } = this.props;
        onFetch();
    }

    public componentWillReceiveProps(nextProps) {
        if (this.props.lastUpdated !== nextProps.lastUpdated) {
            const { onWaitForFetch, refreshDelay, lastUpdated } = nextProps;
            onWaitForFetch(lastUpdated, refreshDelay);
        }
    }

    public render() {
        const { lastUpdated } = this.props;
        const isRefreshing = true;
        return (
            <div className="last-update-info">Naposledny updatováno:<span
                className={isRefreshing ? 'text-success' : 'text-muted'}>
                {lastUpdated}
                </span>
            </div>
        );
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
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
