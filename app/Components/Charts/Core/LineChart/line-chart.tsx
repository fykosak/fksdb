import { axisBottom, axisLeft } from 'd3-axis';
import { ScaleLinear, ScaleTime } from 'd3-scale';
import { select, selectAll } from 'd3-selection';
import { curveBasis, curveMonotoneX } from 'd3-shape';
import ChartComponent from 'FKSDB/Components/Charts/Core/ChartComponent';
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

export default class LineChart<XValue extends Date | number> extends ChartComponent<OwnProps<XValue>, never> {

    private xAxis: SVGGElement;
    private yAxis: SVGGElement;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const {data, xScale, yScale} = this.props;

        yScale.range(this.getInnerYSize());
        xScale.range(this.getInnerXSize());

        const dots = [];
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
                    stroke={datum.color}
                />);
            }
            if (datum.display.area) {
                const areaPath = getAreaPath<XValue>(xScale, yScale, datum.points, yScale(0),
                    datum.curveFactory ? datum.curveFactory : curveMonotoneX);
                areas.push(<path
                    key={index}
                    d={areaPath}
                    className="area"
                    stroke={datum.color}
                    fill={datum.color}
                />);
            }
            if (datum.display.points) {
                datum.points.forEach((point, key) => {
                    dots.push(<circle
                        className={point.active ? 'active' : 'inactive'}
                        key={index + '-' + key}
                        r="7.5"
                        style={{
                            '--point-color-active': point.color.active,
                            '--point-color-inactive': point.color.inactive,
                        } as React.CSSProperties}
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
            <svg viewBox={this.getViewBox()} className="chart">
                <g>
                    <g transform={this.transformXAxis()}
                       className={'axis x-axis'}
                       ref={(xAxis) => this.xAxis = xAxis}/>
                    <g transform={this.transformYAxis()}
                       className={'axis y-axis'}
                       ref={(yAxis) => this.yAxis = yAxis}/>
                    {areas}
                    {lines}
                    {dots}
                </g>
            </svg>
        </div>;
    }

    private getAxis(): void {
        const {xScale, yScale, display} = this.props;
        const xAxis = axisBottom(xScale);
        const yAxis = axisLeft<number>(yScale);

        select(this.xAxis).call(xAxis);
        select(this.yAxis).call(yAxis);

        if (display && display.xGrid) {
            selectAll(".x-axis g.tick")
                .append("line").lower()
                .attr("class","grid-line")
                .attr("y2",(-this.size.height + this.margin.top + this.margin.bottom))
                .attr("stroke","currentcolor");
        }
        if (display && display.yGrid) {
            selectAll(".y-axis g.tick")
                .append("line").lower()
                .attr("class","grid-line")
                .attr("x2",(this.size.width - this.margin.left - this.margin.right))
                .attr("stroke","currentcolor");
        }
    }
}
