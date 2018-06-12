import * as React from 'react';
import Lang from '../../../../../../lang/components/lang';
import Legend from '../legend';
import Chart from './chart';

export default class PointsPie extends React.Component<{}, {}> {

    public render() {
        return (<div>
            <h3><Lang text={'successOfSubmitting'}/></h3>
            <div className="row">
                <Chart/>
                <Legend inline={false}/>
            </div>
        </div>);
    }
}
