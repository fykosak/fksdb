import * as React from 'react';
import { connect } from 'react-redux';
import Loading from '../../helpers/components/loading';
import ResultsShower from '../../helpers/components/results-shower';
import HardVisibleSwitch from '../../helpers/options/compoents/hard-visible-switch';
import { Store as StatisticsStore } from '../reducers';
import ChartsContainer from './charts/';

interface State {
    isReady?: boolean;
    isOrg?: boolean;
}

interface Props {
    mode: string;
}

class App extends React.Component<State & Props, {}> {
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

const mapStateToProps = (state: StatisticsStore): State => {
    return {
        isOrg: state.options.isOrg,
        isReady: state.options.isReady,
    };
};

export default connect(mapStateToProps, null)(App);
