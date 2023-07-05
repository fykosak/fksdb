import { scaleLinear, scaleOrdinal } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import LineChart from 'FKSDB/Components/Charts/Core/LineChart/line-chart';
import { ExtendedPointData, LineChartData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import { EventModel } from 'FKSDB/Models/ORM/Models/EventModel';
import * as React from 'react';
import Legend from 'FKSDB/Components/Charts/Core/Legend/legend';
import { availableLanguage, Translator } from '@translator/translator';

export interface Data {
    events: {
        [eventId: number]: EventModel;
    };
    applications: {
        [eventId: number]: string[];
    };
}

interface OwnProps {
    data: Data;
    translator: Translator<availableLanguage>;
}

export default class TimeProgress extends React.Component<OwnProps, never> {

    public render() {
        const {data} = this.props;

        let minTime = 0;
        let max = 0;
        const lineChartData: LineChartData<number> = [];

        const colorScale = scaleOrdinal(schemeCategory10);
        for (const eventId in data.applications) {
            if (Object.hasOwn(data.applications, eventId) && Object.hasOwn(data.events, eventId)) {
                const event = data.events[eventId];
                const applications = data.applications[eventId];

                const begin = new Date(event.begin);
                let sum = 0;

                const eventData = applications.sort((a, b) => {
                    return ((new Date(a)).getTime() - (new Date(b)).getTime());
                }).map((application): ExtendedPointData<number> => {
                    sum++;
                    const x = ((new Date(application)).getTime() - begin.getTime()) / (1000 * 60 * 60 * 24);
                    minTime = minTime < x ? minTime : x;
                    return {
                        active: false,
                        color: {
                            active: null,
                            inactive: null,
                        },
                        xValue: x,
                        yValue: sum,
                    };
                });
                eventData.push({
                    active: false,
                    color: {
                        active: null,
                        inactive: null,
                    },
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
            <h2>{this.props.translator.getText('Legend')}</h2>
            <Legend data={lineChartData}/>
        </>;
    }
}
