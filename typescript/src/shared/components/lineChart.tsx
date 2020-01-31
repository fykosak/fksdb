import AbstractChart from '@shared/components/chart';
import {
    Axis,
    axisBottom,
    axisLeft,
} from 'd3-axis';
import {
    ScaleLinear,
    ScaleTime,
} from 'd3-scale';
import { select } from 'd3-selection';
import {
    curveBasis, CurveFactory, curveMonotoneX,
    line,
} from 'd3-shape';
import * as React from 'react';
import { getAreaPath, getLinePath, PointData } from '../../apps/fyziklaniResults/statistics/middleware/charts/lines';
import { timeMinute } from 'd3-time';

export type LineChartData = Array<{
    name: string;
    color: string;
    display: {
        points?: boolean;
        lines?: boolean;
        area?: boolean;
    };
    curveFactory?: CurveFactory;
    points: Array<ExtendedPointData<Date | number>>;
}>;

export interface ExtendedPointData<T> extends PointData<T> {
    active?: boolean;
    color?: string;
    label?: string;
}

interface OwnProps<> {
    data: LineChartData;
    xScale: ScaleLinear<number, number> | ScaleTime<number, number>;
    yScale: ScaleLinear<number, number>;
    display?: {
        xGrid: boolean;
        yGrid: boolean;
    };
}

export default class LineChart extends AbstractChart<OwnProps, {}> {

    private xAxis: SVGGElement;
    private yAxis: SVGGElement;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const {data, xScale, yScale, display} = this.props;

        yScale.range(this.getInnerYSize());
        xScale.range(this.getInnerXSize());

        const dots = [];
        const areas = [];
        const lines = [];
        data.forEach((datum, index) => {
            if (datum.display.lines) {
                const lineEl = getLinePath(xScale, yScale, datum.points,
                    datum.curveFactory ? datum.curveFactory : curveBasis);
                lines.push(<path key={index} d={lineEl} className={'line'} stroke={datum.color}/>);
            }
            if (datum.display.area) {
                const areaPath = getAreaPath(xScale, yScale, datum.points, yScale(0),
                    datum.curveFactory ? datum.curveFactory : curveMonotoneX);
                areas.push(<path d={areaPath} className={'area'} stroke={datum.color} fill={datum.color}/>);
            }
            if (datum.display.points) {
                datum.points.forEach((point, key) => {
                    dots.push(<circle
                        key={index + '-' + key}
                        opacity={point.active ? '1' : '0'}
                        r="7.5"
                        fill={point.color}
                        cy={yScale(point.yValue)}
                        cx={xScale(point.xValue)}
                    >
                        <title>
                            {point.label} - {(point.xValue instanceof Date) ? point.xValue.toLocaleTimeString() : point.xValue}
                        </title>
                    </circle>);
                });
            }
        });

        return (
            <svg viewBox={this.getViewBox()} className="chart time-line-histogram">
                <g>
                    {areas}
                    {lines}
                    {dots}
                    <g transform={this.transformXAxis()}
                       className={'axis x-axis ' + ((display && display.xGrid) ? 'grid' : '')}
                       ref={(xAxis) => this.xAxis = xAxis}/>
                    <g transform={this.transformYAxis()}
                       className={'axis y-axis ' + ((display && display.yGrid) ? 'grid' : '')}
                       ref={(yAxis) => this.yAxis = yAxis}/>
                </g>
            </svg>

        );
    }

    private getAxis(): void {
        const {xScale, yScale, display} = this.props;
        const xAxis = axisBottom(xScale);
        const yAxis = axisLeft<number>(yScale)
        if (display && display.xGrid) {
            xAxis.tickSizeInner(-this.size.height + (this.margin.top + this.margin.bottom));
        }
        if (display && display.yGrid) {
            yAxis.tickSizeInner(-this.size.width + (this.margin.left + this.margin.right));
        }
        select(this.xAxis).call(xAxis);
        select(this.yAxis).call(yAxis);
    }
}
