import * as React from 'react';
import Legend from '../legend';
import Chart from './chart';
import { lang } from '../../../../../../i18n/i18n';

export default class PointsPie extends React.Component<{}, {}> {

    public render() {
        return (<div>
            <h3>{lang.getText('successOfSubmitting')}</h3>
            <div className="row">
                <Chart/>
                <Legend inline={false}/>
            </div>
        </div>);
    }
}
