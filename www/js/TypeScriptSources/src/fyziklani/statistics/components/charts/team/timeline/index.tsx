import * as React from 'react';
import { lang } from '../../../../../../i18n/i18n';
import Legend from '../legend';
import Chart from './chart';

export default class TimeLine extends React.Component<{}, {}> {
    public render() {
        return (
            <div>
                <h3>{lang.getText('timeLine')}</h3>
                <div className="row">
                    <Chart/>
                    <Legend inline={true}/>
                </div>
            </div>
        );
    }
}
