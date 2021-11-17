import HardVisibleSwitch from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/HardVisible/Component';
import ResultsShower from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/ResultsShower';
import Timer from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Timer/Timer';
import CorrelationStats from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/CorrelationStatitics/Index';
import TasksStats from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/TaskStatistics/Index';
import TeamStats from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/TeamStatistics/Index';
import * as React from 'react';
import { connect } from 'react-redux';
import { Store as StatisticsStore } from './Reducers';

interface StateProps {
    isOrg: boolean;
}

interface OwnProps {
    mode: 'correlation' | 'team' | 'task';
}

class StatisticsComponentsRouter extends React.Component<StateProps & OwnProps, Record<string, never>> {
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

export default connect(mapStateToProps, null)(StatisticsComponentsRouter);
