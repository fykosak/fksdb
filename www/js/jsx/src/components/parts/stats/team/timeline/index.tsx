import * as React from 'react';

import Chart from './chart';

export default class TimeLine extends React.Component<{}, {}> {
    public render() {
        return (
            <div>
                <h3>TimeLine</h3>
                <div className="row">
                    <Chart/>
                </div>
            </div>
        );
    }
}
