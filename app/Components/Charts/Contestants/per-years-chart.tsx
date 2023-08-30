import Ordinal from '@translator/ordinal';
import { scaleLinear } from 'd3-scale';
import { curveCatmullRom } from 'd3-shape';
import LineChart from 'FKSDB/Components/Charts/Core/LineChart/line-chart';
import Legend from 'FKSDB/Components/Charts/Core/Legend/legend';
import { ExtendedPointData, LineChartData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import * as React from 'react';
import { getMinMaxYear, getSeriesColor, getSeriesLabel, parseData, YearsData } from './contestants-data';
import { Translator } from '@translator/translator';
import { LegendItemDatum } from 'FKSDB/Components/Charts/Core/Legend/item';

export interface OwnProps {
    data: YearsData;
    translator: Translator;
}

export default function PerYearsChart({data, translator}: OwnProps) {

    const {aggregatedSeries, maxValue} = parseData(data);

    const lineChartData: LineChartData<number> & LegendItemDatum[] = [];
    for (const series in aggregatedSeries) {
        if (Object.hasOwn(aggregatedSeries, series)) {
            const points: Array<ExtendedPointData<number>> = [];
            for (const year in aggregatedSeries[series]) {
                if (Object.hasOwn(aggregatedSeries[series], year)) {
                    const label = <>
                        <Ordinal order={+year}/>{' ' + translator.getText('Year')} - {
                        getSeriesLabel(series, translator)}: {
                        aggregatedSeries[series][year]
                    }
                    </>;
                    points.push({
                        color: getSeriesColor(series),
                        label,
                        xValue: +year,
                        yValue: +aggregatedSeries[series][year],
                    });
                }
            }
            lineChartData.push({
                color: getSeriesColor(series),
                curveFactory: curveCatmullRom,
                display: {
                    lines: true,
                    points: true,
                },
                name: getSeriesLabel(series, translator),
                points,
            });
        }
    }
    const [minYear, maxYear] = getMinMaxYear(data);
    const yScale = scaleLinear<number, number>().domain([0, maxValue]);
    const xScale = scaleLinear<number, number>().domain([minYear - 1, maxYear + 1]);

    return <>
        <LineChart<number>
            data={lineChartData}
            xScale={xScale}
            yScale={yScale}
            display={{xGrid: false, yGrid: true}}
        />
        <h2>{translator.getText('Legend')}</h2>
        <Legend data={lineChartData}/>
    </>;
}
