import { lang } from '@i18n/i18n';
import Ordinal from '@i18n/ordinal';
import * as React from 'react';

export type seriesType = string | 'year';

export interface YearsData {
    [year: number]: {
        [series in seriesType]: number;
    };
}

export const getSeriesLabel = (series: seriesType): JSX.Element => {
    if (series === 'year') {
        return <>{lang.getText('Full year')}</>;
    }
    return <>
        <Ordinal order={+series}/>{' ' + lang.getText('series')}
    </>;
};

export const getMinMaxYear = (data: YearsData): [number, number] => {
    const years = Object.keys(data).map((value) => +value);
    return [Math.min(...years), Math.max(...years)];
};

interface AggregatedSeriesData {
    [series: string]: {
        [year: string]: number;
    };
}

interface ParsedData {
    maxValue: number;
    maxSeries: number;
    aggregatedSeries: AggregatedSeriesData;
}

export const parseData = (data: YearsData): ParsedData => {
    let maxValue = 0;
    let maxSeries = 0;
    const aggregatedSeries: AggregatedSeriesData = {};
    for (const year in data) {
        if (data.hasOwnProperty(year)) {
            const datum = data[year];
            for (const series in datum) {
                if (datum.hasOwnProperty(series)) {
                    aggregatedSeries[series] = aggregatedSeries[series] || {};
                    aggregatedSeries[series][year] = datum[series];
                    maxValue = (datum[series] > maxValue) ? datum[series] : maxValue;
                    maxSeries = (+series > maxSeries) ? +series : maxSeries;
                }
            }
        }
    }
    return {maxValue, maxSeries, aggregatedSeries};
};
