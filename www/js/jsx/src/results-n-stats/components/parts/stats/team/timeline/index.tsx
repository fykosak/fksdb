import * as React from 'react';
import Legend from '../legend';

import Chart from './chart';

export default class TimeLine extends React.Component<{}, {}> {
    public render() {
        return (
            <div>
                <h3>TimeLine</h3>
                <div className="row">
                    <Chart/>
                    <Legend inline={true}/>
                </div>
            </div>
        );
    }
}
