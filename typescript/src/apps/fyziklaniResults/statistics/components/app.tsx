import CorrelationStats from '@apps/fyziklaniResults/statistics/components/correlation';
import TasksStats from '@apps/fyziklaniResults/statistics/components/task';
import TeamStats from '@apps/fyziklaniResults/statistics/components/team';
import Timer from '@apps/fyziklaniResults/timer/timer';
import * as React from 'react';
import { connect } from 'react-redux';
import HardVisibleSwitch from '../../hardVisible/component';
import ResultsShower from '../../shared/components/resultsShower';
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
