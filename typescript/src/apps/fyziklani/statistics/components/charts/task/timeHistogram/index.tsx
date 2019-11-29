import { lang } from '@i18n/i18n';
import ChartContainer from '@shared/components/chartContainer';
import * as React from 'react';
import Chart from './chart';

interface OwnProps {
    taskId: number;
    availablePoints: number[];
}

export default class Timeline extends React.Component<OwnProps, {}> {

    public render() {
        const {taskId, availablePoints} = this.props;
        return <ChartContainer
            chart={Chart}
            chartProps={{taskId, availablePoints}}
            includeLegend={true}
            headline={lang.getText('Time histogram')}
        />;
    }
}
