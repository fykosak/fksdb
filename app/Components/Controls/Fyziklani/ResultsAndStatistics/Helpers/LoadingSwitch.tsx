import { FyziklaniResultsCoreStore } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Reducers/coreStore';
import * as React from 'react';
import { connect } from 'react-redux';

interface OwnProps {
    children: any;
}

interface StateProps {
    isReady: boolean;
}

class LoadingSwitch extends React.Component<StateProps & OwnProps, Record<string, never>> {
    public render() {

        const {isReady} = this.props;
        if (!isReady) {
            return <div className="load" style={{textAlign: 'center'}}>
                <img alt="logo" src="/images/fof/logo-animated.svg" style={{width: '50%'}}/>
            </div>;
        }
        return <>{this.props.children}</>;
    }
}

const mapStateToProps = (state: FyziklaniResultsCoreStore): StateProps => {
    return {
        isReady: state.fetchApi.initialLoaded,
    };
};

export default connect(mapStateToProps, null)(LoadingSwitch);
