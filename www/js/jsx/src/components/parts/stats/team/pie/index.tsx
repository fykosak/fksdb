import * as React from 'react';
import Legend from '../legend';
import Chart from './chart';

export default class PointsPie extends React.Component<{}, {}> {

    public render() {
        return (<div>
            <h3>Úspešnosť odovzdávania úloh</h3>
            <div className="row">
                <Chart/>
                <Legend/>
            </div>
        </div>);
    }
}
