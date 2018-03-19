import * as React from 'react';
import { lang } from '../../../../../lang/index';
import Legend from '../legend';
import Chart from './chart';

export default class TimeLine extends React.Component<{}, {}> {
    public render() {
        return (
            <div>
                <h3>{lang.getLang('timeLine')}</h3>
                <div className="row">
                    <Chart/>
                    <Legend inline={true}/>
                </div>
            </div>
        );
    }
}
