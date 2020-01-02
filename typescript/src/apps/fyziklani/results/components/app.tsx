import * as React from 'react';
import { connect } from 'react-redux';
import Loading from '../../helpers/components/loading';
import { FyziklaniResultsStore } from '../reducers';
import Results from './results/';

interface StateProps {
    isReady: boolean;
}

interface OwnProps {
    mode: 'presentation' | 'view';
}

class App extends React.Component<StateProps & OwnProps, {}> {
    public render() {

        const {isReady, mode} = this.props;
        if (!isReady) {
            return <Loading/>;
        }
        return (<Results mode={mode}/>);
    }
}

const mapStateToProps = (state: FyziklaniResultsStore): StateProps => {
    return {
        isReady: state.options.isReady,
    };
};

export default connect(mapStateToProps, null)(App);
