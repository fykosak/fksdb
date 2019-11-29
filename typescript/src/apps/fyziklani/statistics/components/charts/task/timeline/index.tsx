import { lang } from '@i18n/i18n';
import ChartContainer from '@shared/components/chartContainer';
import * as React from 'react';
import Chart from './chart';

interface OwnProps {
    taskId: number;
    availablePoints: number[];
}

export default class Timeline extends React.Component<OwnProps, {}> {
    public constructor(props, context) {
        super(props, context);
        this.state = {from: props.gameStart, to: props.gameEnd};
    }

    public render() {
        const {taskId} = this.props;
        return <ChartContainer
            chart={Chart}
            chartProps={{taskId}}
            includeLegend={true}
            headline={lang.getText('Timeline')}
        />;

    }
}
