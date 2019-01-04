import * as React from 'react';
import { lang } from '../../../../../../i18n/i18n';
import Legend from '../legend';
import Chart from './chart';

interface IProps {
    teamId: number;
}

export default class PointsInTime extends React.Component<IProps, {}> {

    public render() {
        const {teamId} = this.props;
        return (
            <div className={'fyziklani-chart-container'}>
                <h3>{lang.getText('Time progress')}</h3>
                <div className="row">
                    <Chart teamId={teamId}/>
                    <Legend inline={false}/>
                </div>
            </div>
        );
    }
}
