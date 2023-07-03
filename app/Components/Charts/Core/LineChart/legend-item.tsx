import * as React from 'react';
import { ReactNode } from 'react';

interface Props {
    item: {
        name: ReactNode
        description?: string;
        color: string;
        display: {
            points?: boolean;
            lines?: boolean;
            area?: boolean;
            bars?: boolean;
        };
    };
}

export default class LegendItem extends React.Component<Props, never> {
    public render() {
        const {item} = this.props;

        return <div className="chart-legend-item row" style={{'--item-color': item.color} as React.CSSProperties}>
            <div className="col-2 d-flex align-items-center">
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
            {item.description && <span className="col-12">
                <small>{item.description}</small>
            </span>}
        </div>;

    }
}
