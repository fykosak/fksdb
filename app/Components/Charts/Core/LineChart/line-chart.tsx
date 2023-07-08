import { axisBottom, axisLeft } from 'd3-axis';
import { ScaleLinear, ScaleTime } from 'd3-scale';
import { select, selectAll } from 'd3-selection';
import { curveBasis, curveMonotoneX } from 'd3-shape';
import { ChartComponent } from 'FKSDB/Components/Charts/Core/chart-component';
import { getAreaPath, getLinePath, LineChartData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import * as React from 'react';
import './line-chart.scss';

interface OwnProps<XValue extends Date | number> {
    data: LineChartData<XValue>;
    xScale: XValue extends Date ? ScaleTime<number, number> : ScaleLinear<number, number>;
    yScale: ScaleLinear<number, number>;
    display?: {
        xGrid: boolean;
        yGrid: boolean;
    };
}

export default function LineChart<XValue extends Date | number>({data, xScale, yScale, display}: OwnProps<XValue>) {

    yScale.range(ChartComponent.getInnerYSize());
    xScale.range(ChartComponent.getInnerXSize());

    const points = [];
    const areas = [];
    const lines = [];
    data.forEach((datum, index) => {
        if (datum.display.lines) {
            const lineEl = getLinePath<XValue>(xScale, yScale, datum.points,
                datum.curveFactory ? datum.curveFactory : curveBasis);
            lines.push(<path
                key={index}
                d={lineEl}
                className="line"
                style={{'--line-color': datum.color} as React.CSSProperties}
            />);
        }
        if (datum.display.area) {
            const areaPath = getAreaPath<XValue>(xScale, yScale, datum.points, yScale(0),
                datum.curveFactory ? datum.curveFactory : curveMonotoneX);
            areas.push(<path
                key={index}
                d={areaPath}
                className="area"
                style={{'--area-color': datum.color} as React.CSSProperties}
            />);
        }
        if (datum.display.points) {
            datum.points.forEach((point, key) => {
                points.push(<circle
                    className="point"
                    key={index + '-' + key}
                    r="7.5"
                    style={{'--point-color': point.color} as React.CSSProperties}
                    cy={yScale(point.yValue)}
                    cx={xScale(point.xValue)}
                >
                    <title>
                        {point.label
                            ? point.label
                            : ((point.xValue instanceof Date)
                                ? point.xValue.toLocaleTimeString()
                                : point.xValue)
                        }
                    </title>
                </circle>);
            });
        }
    });

    return <div className="line-chart">
        <svg viewBox={ChartComponent.getViewBox()} className="chart">
            <g>
                <g transform={ChartComponent.transformXAxis()}
                   className="axis x-axis"
                   ref={(xAxisRef) => {
                       select(xAxisRef).call(axisBottom(xScale));
                       if (display && display.xGrid) {
                           selectAll('.x-axis g.tick')
                               .append('line').lower()
                               .attr('class', 'grid-line')
                               .attr('y2', (-ChartComponent.size.height + ChartComponent.margin.top + ChartComponent.margin.bottom))
                               .attr('stroke', 'currentcolor');
                       }
                   }}/>
                <g transform={ChartComponent.transformYAxis()}
                   className="axis y-axis"
                   ref={(yAxisRef) => {
                       select(yAxisRef).call(axisLeft<number>(yScale));

                       if (display && display.yGrid) {
                           selectAll('.y-axis g.tick')
                               .append('line').lower()
                               .attr('class', 'grid-line')
                               .attr('x2', (ChartComponent.size.width - ChartComponent.margin.left - ChartComponent.margin.right))
                               .attr('stroke', 'currentcolor');
                       }
                   }}/>
                {areas.length && <g className="areas">
                    {areas}
                </g>}
                {lines.length && <g className="lines">
                    {lines}
                </g>}
                {points.length && <g className="points">
                    {points}
                </g>}
            </g>
        </svg>
    </div>;
}
