import Timer from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Timer/timer';
import CorrelationStats
    from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/components/correlation';
import TasksStats from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/components/task';
import TeamStats from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/components/team';
import * as React from 'react';
import { connect } from 'react-redux';
import HardVisibleSwitch from '../../Helpers/HardVisible/component';
import ResultsShower from '../../Helpers/shared/components/resultsShower';
import { Store as StatisticsStore } from '../reducers';

interface StateProps {
    isOrg: boolean;
}

interface OwnProps {
    mode: 'correlation' | 'team' | 'task';
}

class App extends React.Component<StateProps & OwnProps, {}> {
    public render() {
        const {mode, isOrg} = this.props;
        let content = null;
        switch (mode) {
            case 'team':
            default:
                content = (<TeamStats/>);
                break;
            case 'task':
                content = (<TasksStats/>);
                break;
            case 'correlation':
                content = (<CorrelationStats/>);
        }
        return <>
            {isOrg && <HardVisibleSwitch/>}
            <ResultsShower>
                <div className="container">
                    {content}
                    <Timer mode={'small'}/>
                </div>
            </ResultsShower>
        </>;
    }
}

const mapStateToProps = (state: StatisticsStore): StateProps => {
    return {
        isOrg: state.options.isOrg,
    };
};

export default connect(mapStateToProps, null)(App);
