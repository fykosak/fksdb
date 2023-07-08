import { axisBottom, axisLeft } from 'd3-axis';
import { ScaleLinear, ScaleTime } from 'd3-scale';
import { select, selectAll } from 'd3-selection';
import { ChartComponent } from 'FKSDB/Components/Charts/Core/chart-component';
import * as React from 'react';
import './bar-histogram.scss';

export interface BarItemDatum<XValue extends number | Date> {
    xValue: XValue;
    items: Array<{
        yValue: number;
        label: string;
        color: string;
    }>;
}

interface OwnProps<XValue extends number | Date> {
    xScale: XValue extends Date ? ScaleTime<number, number> : ScaleLinear<number, number>;
    yScale: ScaleLinear<number, number>;
    data: BarItemDatum<XValue>[];
    display?: {
        xGrid: boolean;
        yGrid: boolean;
    };
}

export default function BarHistogram<XValue extends number | Date>({data, yScale, xScale, display}: OwnProps<XValue>) {
    yScale.range(ChartComponent.getInnerYSize());
    xScale.range(ChartComponent.getInnerXSize());

    let maxLength = 0;
    data.forEach((group) => {
        maxLength = maxLength > group.items.length ? maxLength : group.items.length;
    });

    const barXSize = 0.8 / (maxLength + 2);
    const bars = data.map((group, key) => {
        const relativeX = +group.xValue - 0.4;
        const rows = [];
        group.items.forEach((item, index) => {
            const x1 = xScale(relativeX + barXSize * +index);
            const x2 = xScale(relativeX + barXSize * (+index + 1));
            const y1 = yScale(item.yValue);
            const y2 = yScale(0);
            rows.push(<polygon
                key={index}
                points={[[x1, y1], [x1, y2], [x2, y2], [x2, y1]].join(' ')}
                    style={{'--bar-color': item.color} as React.CSSProperties}
                >
                    <title>{item.yValue}</title>
                </polygon>,
            );
        });
        return <g className="bar" key={key}>{rows}</g>;
    });
    return <div className="bar-histogram">
        <svg viewBox={ChartComponent.getViewBox()} className="chart">
            <g>
                <g transform={ChartComponent.transformXAxis()} className="axis x-axis"
                   ref={(xAxisRef) => {
                       const xAxis = axisBottom(xScale);
                       xAxis.tickValues(data.map((group) => group.xValue).map((value) => {
                           return +value;
                       }));
                       select(xAxisRef).call(xAxis);
                       if (display && display.xGrid) {
                           selectAll('.x-axis g.tick')
                               .append('line').lower()
                               .attr('class', 'grid-line')
                               .attr('y2', (-ChartComponent.size.height + ChartComponent.margin.top + ChartComponent.margin.bottom))
                               .attr('stroke', 'currentcolor');
                       }
                   }}/>
                <g transform={ChartComponent.transformYAxis()} className="axis y-axis"
                   ref={(yAxisRef) => {
                       const yAxis = axisLeft<number>(yScale);
                       select(yAxisRef).call(yAxis);
                       if (display && display.yGrid) {
                           selectAll('.y-axis g.tick')
                               .append('line').lower()
                               .attr('class', 'grid-line')
                               .attr('x2', (ChartComponent.size.width - ChartComponent.margin.left - ChartComponent.margin.right))
                               .attr('stroke', 'currentcolor');
                       }
                   }}/>
                {bars}
            </g>
        </svg>
    </div>;
}
