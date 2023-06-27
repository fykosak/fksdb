import * as React from 'react';
import { LineChartData } from './middleware';
import './legend.scss';

interface OwnProps<XValue extends Date | number> {
    data: LineChartData<XValue>;
}

export default class LineChartLegend<XValue extends Date | number> extends React.Component<OwnProps<XValue>, never> {

    public render() {
        const {data} = this.props;

        return (
            <div className="chart-legend chart-legend-line-chart">
                {data.map((item, index) => {
                    return <div key={index} className="chart-legend-item row">
                        <div className="icon icon-line col-2" style={{backgroundColor: item.color}}/>
                        <div className="legend-item-name col-10">{item.name}</div>
                        <div className="legend-item-description col-12">{item.description}</div>
                    </div>;
                })}
            </div>
        );
    }
}
