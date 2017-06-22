import * as React from 'react';
import Legend from '../legend';
import Chart from './chart';

interface IProps {
}

export default class PointsPie extends React.Component<IProps, void> {

    render() {
        return (<div>
            <h3>Úspešnosť odovzdávania úloh</h3>
            <div className="row">
                <Chart/>
                <Legend/>
            </div>
        </div>);
    }
}
