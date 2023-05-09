import Ordinal from '@translator/Ordinal';
import { translator } from '@translator/translator';
import * as React from 'react';

export type seriesType = string | 'year';

export interface YearsData {
    [year: number]: {
        [series in seriesType]: number;
    };
}

export const getSeriesLabel = (series: seriesType): JSX.Element => {
    if (series === 'year') {
        return <>{translator.getText('Full year')}</>;
    }
    return <>
        <Ordinal order={+series}/>{' ' + translator.getText('series')}
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
        if (Object.hasOwn(data,year)) {
            const datum = data[year];
            for (const series in datum) {
                if (Object.hasOwn(datum,series)) {
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
