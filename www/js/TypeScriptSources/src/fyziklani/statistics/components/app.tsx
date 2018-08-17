import * as React from 'react';
import { connect } from 'react-redux';
import Powered from '../../../shared/powered';
import Loading from '../../helpers/components/loading';
import { IFyziklaniStatisticsStore } from '../reducers';
import ChartsContainer from './charts/';

interface IState {
    isReady?: boolean;
}

interface IProps {
    accessKey: string;
    mode: string;
}

class App extends React.Component<IState & IProps, {}> {
    public render() {
        const {isReady, mode} = this.props;
        if (!isReady) {
            return <Loading/>;
        }

        return (<>
            <ChartsContainer mode={mode}/>
            <Powered/>
        </>);
    }
}

const mapStateToProps = (state: IFyziklaniStatisticsStore): IState => {
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
