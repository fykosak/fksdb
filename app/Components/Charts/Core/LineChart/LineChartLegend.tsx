import * as React from 'react';
import { LineChartData } from './middleware';
import './legend.scss';

interface OwnProps<XValue extends Date | number> {
    data: LineChartData<XValue>;
}

export default class LineChartLegend<XValue extends Date | number> extends React.Component<OwnProps<XValue>, never> {

    public render() {
        const {data} = this.props;

        return <div className="chart-legend chart-legend-line-chart">
            {data.map((item, index) => {
                return <div key={index} className="chart-legend-item row">
                    <div className="col-2 d-flex align-items-center">
                        {item.display.lines &&
                            <span className="icon icon-line" style={{backgroundColor: item.color}}/>
                        }
                        {item.display.points &&
                            <span className="icon icon-point" style={{backgroundColor: item.color}}/>
                        }
                        {item.display.area &&
                            <span className="icon icon-area" style={{backgroundColor: item.color}}/>
                        }
                        {item.display.bars &&
                            <span className="icon icon-bar" style={{backgroundColor: item.color}}/>
                        }
                    </div>
                    <div className="col-10">
                        <strong>{item.name}</strong>
                    </div>
                    <div className="col-12">
                        <small>{item.description}</small>
                    </div>
                </div>;
            })}
        </div>;
    }
}
