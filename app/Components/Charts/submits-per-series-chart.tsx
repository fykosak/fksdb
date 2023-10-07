import { scaleLinear, scaleTime } from 'd3-scale';
import LineChart from 'FKSDB/Components/Charts/Core/LineChart/line-chart';
import Legend from 'FKSDB/Components/Charts/Core/Legend/legend';
import { LineChartData, PointData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import * as React from 'react';
import { Translator } from '@translator/translator';
import { LegendItemDatum } from 'FKSDB/Components/Charts/Core/Legend/item';
import {getSeriesColor, getSeriesLabel} from './Contestants/contestants-data';

interface Data {
	deadlines: {
        [series: number]: string;
    };
    submits: Array<{
        submittedOn: string;
        series: number;
    }>;
}

interface OwnProps {
    data: Data[];
    translator: Translator;
}

export default function SubmitsPerSeries({data, translator}: OwnProps) {
    const lineChartData: LineChartData<number> & LegendItemDatum[] = [];
    const submitsInSeries: PointData<number>[][] = [];
    const submitCounts: number[] = [];

    const maxSeries = Number(Object.keys(data['deadlines']).at(-1));
    let maxSubmitCount = 0;

    for (let series = 0; series <= maxSeries; series++) {
        submitsInSeries.push([]);
        submitCounts.push(0);
    }

    let minTime = 0;

    data['submits'].forEach((submit: {submittedOn: string; series: number;}) => {
        const delta = (new Date(submit.submittedOn).getTime() - new Date(data['deadlines'][submit.series]).getTime()) / (1000 * 3600 * 24);
        minTime = minTime < delta ? minTime : delta;
        submitCounts[submit.series] += 1;
        submitsInSeries[submit.series].push({
            xValue: delta,
            yValue: submitCounts[submit.series],
        });
    });

    submitCounts.forEach((count) => {
        if (count > maxSubmitCount) {
            maxSubmitCount = count;
        }
    });

    const display = {
        area: false,
        lines: true,
        points: false,
    };

    for (let series = 1; series <= maxSeries; series++) {
        lineChartData.push({
            color: getSeriesColor(series.toString()),
            display,
            name: getSeriesLabel(series.toString(), translator),
            points: submitsInSeries[series],
        });
    }

    const yScale = scaleLinear<number, number>().domain([0, maxSubmitCount]);
    const xScale = scaleLinear<number, number>().domain([minTime, 0]);

    return <>
        <LineChart<number>
            data={lineChartData}
            xScale={xScale}
            yScale={yScale}
            display={{
                xGrid: true,
                yGrid: true,
            }}
        />
        <h2>{translator.getText('Legend')}</h2>
        <Legend data={lineChartData}/>
    </>;
}
