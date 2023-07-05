import { scaleLinear } from 'd3-scale';
import BarHistogram from 'FKSDB/Components/Charts/Core/BarHistogram/bar-histogram';
import Legend from 'FKSDB/Components/Charts/Core/Legend/legend';
import { LineChartData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import * as React from 'react';
import { getMinMaxYear, getSeriesColor, getSeriesLabel, parseData, YearsData } from './contestatns-data';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    data: YearsData;
    translator: Translator<availableLanguage>;
}

export default class PerSeriesChart extends React.Component<OwnProps, never> {

    public render() {
        const {data, translator} = this.props;
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
                            color: getSeriesColor(series),
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
                color: getSeriesColor(series.toString()),
                display: {
                    bars: true,
                },
                name: getSeriesLabel(series.toString(), this.props.translator),
                points: [],
            });
        }

        return <>
            <BarHistogram
                xScale={xScale}
                yScale={yScale}
                data={histogramData}
                display={{
                    xGrid: false, yGrid: true,
                }}
            />
            <h3>{translator.getText('Legend')}</h3>
            <Legend data={legendData}/>
        </>;
    }
}
