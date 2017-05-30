import * as React from 'react';
import {connect} from 'react-redux';
import {
    fetchResults,
    waitForFetch
} from '../../helpers/fetch';

interface IProps {
    lastUpdated?: string;
    refreshDelay: number;
    onFetch?: Function;
    onWaitForFetch?: Function;
}

class Downloader extends React.Component<IProps,any> {
    public componentDidMount() {
        const {onFetch}= this.props;
        onFetch(null);
    }

    public componentWillReceiveProps(nextProps) {
        if (this.props.lastUpdated !== nextProps.lastUpdated) {
            const {onWaitForFetch, refreshDelay, lastUpdated} = nextProps;
            onWaitForFetch(lastUpdated, refreshDelay)
        }
    }

    public render() {
        const {lastUpdated} = this.props;
        const isRefreshing = true;
        return (
            <div className="last-update-info">Naposledny updatováno:<span
                className={isRefreshing?'text-success':'text-muted'}>
                {lastUpdated}
                </span>
            </div>
        );
    };
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        lastUpdated: state.downloader.lastUpdated,
        refreshDelay: state.downloader.refreshDelay,
    }
};

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
        onFetch: () => fetchResults(dispatch, null),
        onWaitForFetch: (lastUpdated, delay) => waitForFetch(dispatch, delay, lastUpdated),
    };
};

export default connect(
    mapStateToProps,
    mapDispatchToProps,
)(Downloader);
