import * as React from 'react';

import Chart from './chart';

interface IProps {
}

export default class TimeLine extends React.Component<IProps, void> {
    render() {
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
