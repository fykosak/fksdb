import { lang } from '@i18n/i18n';
import ChartContainer from '@shared/components/chartContainer';
import * as React from 'react';
import Chart from './chart';

interface OwnProps {
    teamId: number;
}

export default class TimeLine extends React.Component<OwnProps, {}> {
    public render() {
        const {teamId} = this.props;
        return <ChartContainer
            chart={Chart}
            chartProps={{teamId}}
            includeLegend={true}
            headline={lang.getText('Timeline')}
        />;
    }
}
