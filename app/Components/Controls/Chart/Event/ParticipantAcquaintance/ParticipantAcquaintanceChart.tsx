import ChartContainer from '@FKSDB/Components/Controls/Chart/Core/ChartContainer';
import InnerParticipantAcquaintanceChart, { OwnProps } from '@FKSDB/Components/Controls/Chart/Event/ParticipantAcquaintance/InnerParticipantAcquaintanceChart';
import * as React from 'react';

export default class ParticipantAcquaintanceChart extends React.Component<OwnProps, {}> {

    public render() {
        return <ChartContainer
            chart={InnerParticipantAcquaintanceChart}
            chartProps={{...this.props}}
            containerClassName="chart-participant-acquaintance"
        />;
    }
}
