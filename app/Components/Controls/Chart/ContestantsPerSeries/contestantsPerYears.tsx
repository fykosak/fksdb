import { getMinMaxYear, getSeriesLabel, parseData, seriesType, YearsData } from '@apps/chart/contestatnsData';
import { lang } from '@i18n/i18n';
import Ordinal from '@i18n/ordinal';
import LineChart from '@shared/components/lineChart/lineChart';
import { ExtendedPointData, LineChartData } from '@shared/components/lineChart/interfaces';
import LineChartLegend from '@shared/components/lineChart/lineChartLegend';
import { scaleLinear, scaleOrdinal } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import { curveCatmullRom } from 'd3-shape';
import * as React from 'react';

export interface OwnProps {
    data: YearsData;
}

export default class ContestantsPerYears extends React.Component<OwnProps, {}> {

    public render() {
        const {data} = this.props;
        const colorScale = scaleOrdinal(schemeCategory10);
        const colorCallback = (series: seriesType) => {
            return series === 'year' ? '#000' : colorScale(series);
        };
        const {aggregatedSeries, maxValue} = parseData(data);

        const lineChartData: LineChartData = [];
        for (const series in aggregatedSeries) {
            if (aggregatedSeries.hasOwnProperty(series)) {
                const points: Array<ExtendedPointData<number>> = [];
                for (const year in aggregatedSeries[series]) {
                    if (aggregatedSeries[series].hasOwnProperty(year)) {
                        const label = <>
                            <Ordinal order={+year}/>{' ' + lang.getText('Year')} - {
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

        return <div className="row">
            <div className="chart-container col-lg-9 col-md-8">
                <LineChart data={lineChartData} xScale={xScale} yScale={yScale} display={{xGrid: false, yGrid: true}}/>
            </div>
            <div className="chart-legend-container col-lg-3 col-md-4">
                <LineChartLegend data={lineChartData}/>
            </div>
        </div>;
    }
}
