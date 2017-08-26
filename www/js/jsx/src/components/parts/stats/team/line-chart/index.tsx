import * as React from 'react';
import Legend from '../legend';
import Chart from './chart';

export default class PointsInTime extends React.Component<{}, {}> {

    public render() {
        return (
            <div>
                <h3>Time progress number of points</h3>
                <div className="row">
                    <Chart/>
                    <Legend/>
                </div>
            </div>
        );
    }
}
