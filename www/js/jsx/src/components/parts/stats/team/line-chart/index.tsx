import * as React from 'react';
import Legend from '../legend';
import Chart from './chart';

interface IProps {
}

export default class PointsInTime extends React.Component<IProps, void> {

    render() {
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
