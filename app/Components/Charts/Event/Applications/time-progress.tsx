import { scaleLinear, scaleOrdinal } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import LineChart from 'FKSDB/Components/Charts/Core/LineChart/line-chart';
import { ExtendedPointData, LineChartData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import { EventModel } from 'FKSDB/Models/ORM/Models/event-model';
import * as React from 'react';
import Legend from 'FKSDB/Components/Charts/Core/Legend/legend';
import { availableLanguage, Translator } from '@translator/translator';
import { LegendItemDatum } from 'FKSDB/Components/Charts/Core/Legend/item';

export interface Data {
    events: {
        [eventId: number]: EventModel;
    };
    applications: {
        [eventId: number]: Array<{
            created: string;
            createdBefore: number;
        }>;
    };
}

interface OwnProps {
    data: Data;
    translator: Translator<availableLanguage>;
}

export default function TimeProgress({data, translator}: OwnProps) {
    let minTime = 0;
    let max = 0;
    const lineChartData: LineChartData<number> & LegendItemDatum[] = [];

    const colorScale = scaleOrdinal(schemeCategory10);
    for (const eventId in data.applications) {
        if (Object.hasOwn(data.applications, eventId) && Object.hasOwn(data.events, eventId)) {
            const event = data.events[eventId];
            const applications = data.applications[eventId];
            let sum = 0;

            const eventData = applications.sort((a, b) => {
                return a.createdBefore - b.createdBefore;
            }).map((application): ExtendedPointData<number> => {
                sum++;
                const delta = application.createdBefore / (3600 * 24);
                minTime = minTime < delta ? minTime : delta;
                return {
                    color: null,
                    xValue: delta,
                    yValue: sum,
                };
            });
            eventData.push({
                color: null,
                xValue: 0,
                yValue: sum,
            });
            max = max > eventData.length ? max : eventData.length;
            lineChartData.push({
                color: colorScale(eventId),
                display: {
                    area: false,
                    lines: true,
                    points: false,
                },
                name: event.name,
                points: eventData,
            });
        }
    }

    const yScale = scaleLinear<number, number>().domain([0, max]);
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
