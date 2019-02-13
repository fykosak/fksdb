import * as React from 'react';
import { connect } from 'react-redux';
import Loading from '../../helpers/components/loading';
import { FyziklaniResultsStore } from '../reducers';
import Results from './results/Index';

interface State {
    isReady?: boolean;
}

interface Props {
    mode: string;
}

class App extends React.Component<State & Props, {}> {
    public render() {

        const {isReady, mode} = this.props;
        if (!isReady) {
            return <Loading/>;
        }
        return (<Results mode={mode}/>);
    }
}

const mapStateToProps = (state: FyziklaniResultsStore): State => {
    return {
        isReady: state.options.isReady,
    };
};

export default connect(mapStateToProps, null)(App);
