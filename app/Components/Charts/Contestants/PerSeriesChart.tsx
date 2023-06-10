import { scaleLinear, scaleOrdinal } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import BarHistogram from 'FKSDB/Components/Charts/Core/BarHistogram/BarHistogram';
import ChartContainer from 'FKSDB/Components/Charts/Core/ChartContainer';
import LineChartLegend from 'FKSDB/Components/Charts/Core/LineChart/LineChartLegend';
import { LineChartData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import * as React from 'react';
import { getMinMaxYear, getSeriesLabel, parseData, YearsData } from './ContestatnsData';

interface OwnProps {
    data: YearsData;
}

export default class PerSeriesChart extends React.Component<OwnProps> {

    public render() {
        const colorScale = scaleOrdinal(schemeCategory10);
        const {data} = this.props;
        const {maxValue, maxSeries} = parseData(data);
        const [minYear, maxYear] = getMinMaxYear(data);
        const yScale = scaleLinear<number, number>().domain([0, maxValue]);
        const xScale = scaleLinear<number, number>().domain([minYear - 1, maxYear]);

        const histogramData = [];
        for (const year in data) {
            if (Object.hasOwn(data,year)) {
                const histogramItems = [];
                const datum = data[year];
                for (const series in datum) {
                    if (Object.hasOwn(datum,series)) {
                        histogramItems.push({
                            color: colorScale(series),
                            label: series,
                            yValue: datum[series],
                        });
                    }
                }
                histogramData.push({xValue: year, items: histogramItems});
            }
        }

        const legendData: LineChartData<number> = [];
        for (let series = 1; series <= maxSeries; series++) {
            legendData.push({
                color: colorScale(series.toString()),
                display: {},
                name: getSeriesLabel(series.toString()),
                points: [],
            });
        }

        return <ChartContainer
            chart={BarHistogram}
            chartProps={{xScale,
                yScale,
                data: histogramData,
                display: {
                    xGrid: false, yGrid: true
                }
            }}
            legendComponent={LineChartLegend}
            legendProps={{data: legendData}}
        />;
    }
}
