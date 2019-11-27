import * as React from 'react';
import { connect } from 'react-redux';
import Loading from '../../helpers/components/loading/';
import ResultsShower from '../../helpers/components/resultsShower/index';
import HardVisibleSwitch from '../../helpers/options/compoents/hardVisibleSwitch';
import { Store as StatisticsStore } from '../reducers';
import ChartsContainer from './charts/';

interface StateProps {
    isReady: boolean;
    isOrg: boolean;
}

interface OwnProps {
    mode: string;
}

class App extends React.Component<StateProps & OwnProps, {}> {
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

const mapStateToProps = (state: StatisticsStore): StateProps => {
    return {
        isOrg: state.options.isOrg,
        isReady: state.options.isReady,
    };
};

export default connect(mapStateToProps, null)(App);
