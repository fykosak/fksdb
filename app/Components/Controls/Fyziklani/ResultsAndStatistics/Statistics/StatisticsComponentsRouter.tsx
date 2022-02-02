import HardVisibleSwitch from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/HardVisible/Component';
import Timer from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Timer/Timer';
import CorrelationStats
    from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Statistics/CorrelationStatitics/Index';
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

class StatisticsComponentsRouter extends React.Component<StateProps & OwnProps> {
    public render() {
        const {mode} = this.props;
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
            <Timer mode={'small'}/>
            <div className="container">
                {content}
                <Timer mode={'small'}/>
            </div>
        </>;
    }
}

const mapStateToProps = (state: StatisticsStore): StateProps => {
    return {
        isOrg: state.options.isOrg,
    };
};

export default connect(mapStateToProps, null)(StatisticsComponentsRouter);
