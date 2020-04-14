import * as React from 'react';
import { connect } from 'react-redux';
import { FyziklaniResultsCoreStore } from '../reducers/coreStore';
import Loading from './loading';

interface StateProps {
    isReady: boolean;
}

class LoadingSwitch extends React.Component<StateProps, {}> {
    public render() {

        const {isReady} = this.props;
        if (!isReady) {
            return <Loading/>;
        }
        return <>{this.props.children}</>;
    }
}

const mapStateToProps = (state: FyziklaniResultsCoreStore): StateProps => {
    return {
        isReady: state.options.isReady,
    };
};

export default connect(mapStateToProps, null)(LoadingSwitch);
