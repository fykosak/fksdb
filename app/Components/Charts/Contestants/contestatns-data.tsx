import Ordinal from '@translator/Ordinal';
import * as React from 'react';
import { availableLanguage, Translator } from '@translator/translator';

export type seriesType = string | 'year';

export interface YearsData {
    [year: number]: {
        [series in seriesType]: number;
    };
}

export const getSeriesLabel = (series: seriesType, translator: Translator<availableLanguage>): JSX.Element => {
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

export const getSeriesColor = (series: number): string => {
    switch (series) {
        case 1:
            return;
        case 2:
            return;
        case 3:
            return;
        case 4:
            return;
        case 5:
            return;
        case 6:
            return;
        default:
            return;
    }
}
