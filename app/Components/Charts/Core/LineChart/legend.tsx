import * as React from 'react';
import { LineChartData } from './middleware';
import './legend.scss';
import LegendItem from 'FKSDB/Components/Charts/Core/LineChart/legend-item';

interface OwnProps<XValue extends Date | number> {
    data: LineChartData<XValue>;
}

export default class Legend<XValue extends Date | number> extends React.Component<OwnProps<XValue>, never> {
    public render() {
        const {data} = this.props;

        return <div className="chart-legend row row-cols-lg-5">
            {data.map((item, index) => {
                return <LegendItem item={item} key={index}/>
            })}
        </div>;
    }

}
