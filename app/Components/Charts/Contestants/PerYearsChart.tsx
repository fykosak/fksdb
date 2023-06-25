import Ordinal from '@translator/Ordinal';
import { scaleLinear, scaleOrdinal } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import { curveCatmullRom } from 'd3-shape';
import ChartContainer from 'FKSDB/Components/Charts/Core/ChartContainer';
import LineChart from 'FKSDB/Components/Charts/Core/LineChart/LineChart';
import LineChartLegend from 'FKSDB/Components/Charts/Core/LineChart/LineChartLegend';
import { ExtendedPointData, LineChartData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import * as React from 'react';
import { getMinMaxYear, getSeriesLabel, parseData, seriesType, YearsData } from './ContestatnsData';
import { availableLanguage, Translator } from '@translator/translator';

export interface OwnProps {
    data: YearsData;
    translator: Translator<availableLanguage>;
}

export default class PerYearsChart extends React.Component<OwnProps, never> {

    public render() {
        const {data, translator} = this.props;
        const colorScale = scaleOrdinal(schemeCategory10);
        const colorCallback = (series: seriesType) => {
            return series === 'year' ? '#000' : colorScale(series);
        };
        const {aggregatedSeries, maxValue} = parseData(data);

        const lineChartData: LineChartData<number> = [];
        for (const series in aggregatedSeries) {
            if (Object.hasOwn(aggregatedSeries,series)) {
                const points: Array<ExtendedPointData<number>> = [];
                for (const year in aggregatedSeries[series]) {
                    if (Object.hasOwn(aggregatedSeries[series],year)) {
                        const label = <>
                            <Ordinal order={+year}/>{' ' + translator.getText('Year')} - {
                            getSeriesLabel(series)}: {
                            aggregatedSeries[series][year]
                        }
                        </>;
                        points.push({
                            active: true,
                            color: colorCallback(series),
                            label,
                            xValue: +year,
                            yValue: +aggregatedSeries[series][year],
                        });
                    }
                }
                lineChartData.push({
                    color: colorCallback(series),
                    curveFactory: curveCatmullRom,
                    display: {
                        lines: true,
                        points: true,
                    },
                    name: getSeriesLabel(series),
                    points,
                });
            }
        }
        const [minYear, maxYear] = getMinMaxYear(data);
        const yScale = scaleLinear<number, number>().domain([0, maxValue]);
        const xScale = scaleLinear<number, number>().domain([minYear - 1, maxYear + 1]);

        return <ChartContainer chart={LineChart} chartProps={{
            data: lineChartData,
            display: {xGrid: false, yGrid: true},
            xScale,
            yScale,
        }} legendComponent={LineChartLegend} legendProps={{
            data: lineChartData,
        }} containerClassName="contestants-per-year"/>;
    }
}
