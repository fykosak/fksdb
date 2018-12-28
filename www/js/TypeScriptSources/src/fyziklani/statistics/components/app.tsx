import * as React from 'react';
import { connect } from 'react-redux';
import Loading from '../../helpers/components/loading';
import { IFyziklaniStatisticsStore } from '../reducers';
import ChartsContainer from './charts/';
import ResultsShower from '../../helpers/components/results-shower';
import HardVisibleSwitch from '../../helpers/options/compoents/hard-visible-switch';

interface IState {
    isReady?: boolean;
    isOrg?: boolean;
}

interface IProps {
    mode: string;
}

class App extends React.Component<IState & IProps, {}> {
    public render() {
        const {isReady, mode, isOrg} = this.props;
        if (!isReady) {
            return <Loading/>;
        }
        return <>
            {isOrg && <HardVisibleSwitch/>}
            <ResultsShower>
                <ChartsContainer mode={mode}/>
            </ResultsShower></>;
    }
}

const mapStateToProps = (state: IFyziklaniStatisticsStore): IState => {
    return {
        isOrg: state.options.isOrg,
        isReady: state.options.isReady,
    };
};

export default connect(mapStateToProps, null)(App);
