import * as React from 'react';
import { lang } from '../../../../../lang/index';
import Legend from '../legend';
import Chart from './chart';

export default class PointsInTime extends React.Component<{}, {}> {

    public render() {
        return (
            <div>
                <h3>{lang.getLang('timeProgress')}</h3>
                <div className="row">
                    <Chart/>
                    <Legend inline={false}/>
                </div>
            </div>
        );
    }
}
