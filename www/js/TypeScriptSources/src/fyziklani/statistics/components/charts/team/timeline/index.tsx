import * as React from 'react';
import { lang } from '../../../../../../i18n/i18n';
import Legend from '../legend';
import Chart from './chart';

export default class TimeLine extends React.Component<{}, {}> {
    public render() {
        return (
            <div className={'fyziklani-chart-container'}>
                <h3>{lang.getText('timeLine')}</h3>
                <Chart/>
                <Legend inline={true}/>
            </div>
        );
    }
}
