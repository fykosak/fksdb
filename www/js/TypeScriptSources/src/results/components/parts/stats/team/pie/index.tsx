import * as React from 'react';
import { lang } from '../../../../../lang/index';
import Legend from '../legend';
import Chart from './chart';

export default class PointsPie extends React.Component<{}, {}> {

    public render() {
        return (<div>
            <h3>{lang.getLang('successOfSubmitting')}</h3>
            <div className="row">
                <Chart/>
                <Legend inline={false}/>
            </div>
        </div>);
    }
}
