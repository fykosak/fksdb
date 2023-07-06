import { scaleBand, scaleLinear, scaleOrdinal } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import { EventModel } from 'FKSDB/Models/ORM/Models/event-model';
import * as React from 'react';
import { useState } from 'react';
import { availableLanguage, Translator } from '@translator/translator';
import Range from 'FKSDB/Components/Charts/Event/Applications/range';
import './bar-progress.scss';
import ChartComponent from 'FKSDB/Components/Charts/Core/chart-component';

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

export default function BarProgress(props: OwnProps) {

    const {data, translator} = props;
    const [time, setTime] = useState<number>(0);
    let minDelta = 0;
    const colorScale = scaleOrdinal(schemeCategory10);
    const barData: Array<{ eventId: string, sum: number, color: string }> = [];
    for (const eventId in data.applications) {

        if (Object.hasOwn(data.applications, eventId)) {
            const applications = data.applications[eventId];
            const eventData = applications.reduce<number>((sum, application): number => {
                const delta = application.createdBefore / (60 * 60);
                minDelta = minDelta < delta ? minDelta : delta;
                if (delta < time) {
                    sum++;
                }
                return sum;
            }, 0);
            barData.push({eventId: eventId, sum: eventData, color: colorScale(eventId)});
        }
    }

    const xScale = scaleLinear<number, number>()
        .domain([0, Math.max(...barData.map(({sum}) => sum)) || 1])
        .range(ChartComponent.getInnerXSize());
    const yScale = scaleBand<string>()
        .domain([...barData].sort((a, b) => a.sum - b.sum).map(({eventId}) => eventId))
        .range(ChartComponent.getInnerYSize())
        .padding(0.1);

    const bars = barData.map(({eventId, sum, color}) => {
        const x1 = xScale(0);
        const x2 = xScale(sum);
        const y1 = yScale(eventId);
        const y2 = yScale.bandwidth();
        return <g transform={'translate(' + x1 + ',' + y1 + ')'} key={eventId} className="bar">
            <rect
                fill={color}
                height={y2}
                width={x2 - x1 + 5}
            />
            <text
                x="10"
                y={yScale.bandwidth() / 2}
            >{props.data.events[eventId].name}: {sum}</text>
        </g>;
    });

    return <>
        <Range min={minDelta} onChange={(value) => setTime(value)} value={time} translator={translator}/>
        <div className="bar-progress">
            <svg viewBox={ChartComponent.getViewBox()}>
                <g>{bars}</g>
            </svg>
        </div>
    </>;
}
