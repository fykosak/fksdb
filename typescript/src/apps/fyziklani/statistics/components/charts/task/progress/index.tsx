import { lang } from '@i18n/i18n';
import ChartContainer from '@shared/components/chartContainer';
import * as React from 'react';
import Chart from './chart';

interface OwnProps {
    availablePoints: number[];
}

export default class TaskStats extends React.Component<OwnProps, {}> {
    public render() {
        const {availablePoints} = this.props;
        return <ChartContainer
            chart={Chart}
            chartProps={{availablePoints}}
            includeLegend={true}
            headline={lang.getText('Total solved problem')}
        />;
    }
}
