import * as React from 'react';
import { LineChartData } from './middleware';
import './legend.scss';

interface OwnProps<XValue extends Date | number> {
    data: LineChartData<XValue>;
}

export default class Legend<XValue extends Date | number> extends React.Component<OwnProps<XValue>, never> {

    public render() {
        const {data} = this.props;

        return <div className="chart-legend chart-legend-line-chart">
            {data.map((item, index) => {
                return <div key={index} className="chart-legend-item row">
                    <div className="col-2 d-flex align-items-center"
                         style={{'--item-color': item.color} as React.CSSProperties}>
                        {item.display.lines &&
                            <span className="icon icon-line"/>
                        }
                        {item.display.points &&
                            <span className="icon icon-point"/>
                        }
                        {item.display.area &&
                            <span className="icon icon-area"/>
                        }
                        {item.display.bars &&
                            <span className="icon icon-bar"/>
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
