import * as React from 'react';
import { connect } from 'react-redux';
import Powered from '../../../shared/powered';
import Loading from '../../helpers/components/loading';
import { IFyziklaniResultsStore } from '../reducers';
import Results from './results/';
import FilterSelect from './results/filter/select';

interface IState {
    isReady?: boolean;
}

interface IProps {
    accessKey: string;
}

class App extends React.Component<IState & IProps, {}> {
    public render() {
        // <NavBar/>
        if (!this.props.isReady) {
            return <Loading/>;
        }
        return (<>
            <FilterSelect/>
            <Results accessKey={this.props.accessKey} basePath={'/'}/>
            <Powered/>
        </>);
    }
}

const mapStateToProps = (state: IFyziklaniResultsStore): IState => {
    return {
        isReady: state.options.isReady,
    };
};

export default connect(
    mapStateToProps,
    (): IState => {
        return {};
    },
)(App);
