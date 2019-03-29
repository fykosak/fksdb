import * as React from 'react';
import { lang } from '../../../../../../i18n/i18n';
import Legend from '../legend';
import Chart from './chart';

interface Props {
    teamId: number;
}

export default class TimeLine extends React.Component<Props, {}> {
    public render() {
        const {teamId} = this.props;
        return (
            <div className={'fyziklani-chart-container'}>
                <h3>{lang.getText('Timeline')}</h3>
                <Chart teamId={teamId}/>
                <Legend inline={true}/>
            </div>
        );
    }
}
