import { getMinMaxYear, getSeriesLabel, parseData, YearsData } from '@apps/chart/contestatnsData';
import AbstractChart from '@shared/components/chart';
import { LineChartData } from '@shared/components/lineChart/interfaces';
import LineChartLegend from '@shared/components/lineChart/lineChartLegend';
import { axisBottom, axisLeft } from 'd3-axis';
import { ScaleLinear, scaleLinear, scaleOrdinal } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import { select } from 'd3-selection';
import * as React from 'react';

interface OwnProps {
    data: YearsData;
}

export default class ContestantsPerSeries extends AbstractChart<OwnProps, {}> {

    private xAxis: SVGGElement;
    private yAxis: SVGGElement;

    private xScale: ScaleLinear<number, number>;
    private yScale: ScaleLinear<number, number>;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const colorScale = scaleOrdinal(schemeCategory10);
        const {data} = this.props;
        const {maxValue, maxSeries} = parseData(data);
        const [minYear, maxYear] = getMinMaxYear(data);
        this.yScale = scaleLinear<number, number>().domain([0, maxValue]).range(this.getInnerYSize());
        this.xScale = scaleLinear<number, number>().domain([minYear - 1, maxYear]).range(this.getInnerXSize());

        const barXSize = 0.8 / (maxSeries + 2);
        const bars = [];
        for (const year in data) {
            if (data.hasOwnProperty(year)) {
                const relativeYear = +year - 0.5;
                const datum = data[year];
                const rows = [];
                for (const series in datum) {
                    if (datum.hasOwnProperty(series)) {
                        const x1 = this.xScale(relativeYear + barXSize * +series);
                        const x2 = this.xScale(relativeYear + barXSize * (+series + 1));
                        const y1 = this.yScale(datum[series]);
                        const y2 = this.yScale(0);
                        rows.push(<polygon
                            key={series}
                            points={[[x1, y1], [x1, y2], [x2, y2], [x2, y1]].join(' ')}
                            fill={colorScale(series)}/>);
                    }
                }
                bars.push(rows);
            }
        }

        const legendData: LineChartData = [];
        for (let series = 1; series <= maxSeries; series++) {
            legendData.push({
                color: colorScale(series.toString()),
                display: {},
                name: getSeriesLabel(series.toString()),
                points: [],
            });
        }

        return <div className="row">
            <div className="chart-container col-lg-9 col-md-8">
                <svg viewBox={this.getViewBox()} className="chart time-histogram">
                    <g>
                        {bars}
                        <g transform={this.transformXAxis()} className="x-axis" ref={(xAxis) => this.xAxis = xAxis}/>
                        <g transform={this.transformYAxis()} className="y-axis" ref={(yAxis) => this.yAxis = yAxis}/>
                    </g>
                </svg>
            </div>
            <div className="chart-legend-container col-lg-3 col-md-4">
                <LineChartLegend data={legendData}/>
            </div>
        </div>;
    }

    private getAxis(): void {
        const xAxis = axisBottom<number>(this.xScale);
        xAxis.tickValues(Object.keys(this.props.data).map((value) => +value));
        select(this.xAxis).call(xAxis);

        const yAxis = axisLeft<number>(this.yScale);
        select(this.yAxis).call(yAxis);
    }
}
