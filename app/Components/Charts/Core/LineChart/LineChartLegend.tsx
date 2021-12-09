import * as React from 'react';
import { LineChartData } from './middleware';

interface OwnProps<XValue extends Date | number> {
    data: LineChartData<XValue>;
}

export default class LineChartLegend<XValue extends Date | number> extends React.Component<OwnProps<XValue>> {

    public render() {
        const {data} = this.props;

        return (
            <div className="legend line-chart-legend">
                {data.map((item, index) => {
                    return <div key={index} className="legend-item row">
                        <div className="legend-item-icon col-4">
                            <div className="line" style={{backgroundColor: item.color}}/>
                        </div>
                        <div className="legend-item-name col-8">{item.name}</div>
                        <div className="legend-item-description col-12">{item.description}</div>
                    </div>;
                })}
            </div>
        );
    }
}
