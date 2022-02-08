import { translator } from '@translator/translator';
import {
    scaleLinear,
    scaleOrdinal,
} from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import ChartContainer from 'FKSDB/Components/Charts/Core/ChartContainer';
import LineChart from 'FKSDB/Components/Charts/Core/LineChart/LineChart';
import { LineChartData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import { ModelEvent } from 'FKSDB/Models/ORM/Models/modelEvent';
import * as React from 'react';
import LineChartLegend from 'FKSDB/Components/Charts/Core/LineChart/LineChartLegend';

export interface Data {
    events: {
        [eventId: number]: ModelEvent;
    };
    teams?: {
        [eventId: number]: Array<{
            created: string;
        }>;
    };
    participants?: {
        [eventId: number]: Array<{
            created: string;
        }>;
    };
}

interface OwnProps {
    data: Data;
    accessKey: 'participants' | 'teams';
}

export default class CommonChart extends React.Component<OwnProps> {

    public render() {
        const {data, accessKey} = this.props;

        let minTime = 0;
        let max = 0;
        const lineChartData: LineChartData<number> = [];

        const colorScale = scaleOrdinal(schemeCategory10);
        for (const eventId in data[accessKey]) {
            if (data[accessKey].hasOwnProperty(eventId) && data.events.hasOwnProperty(eventId)) {
                const event = data.events[eventId];
                const apps = data[accessKey][eventId];

                const begin = new Date(event.begin);
                let sum = 0;

                const eventData = apps.sort((a, b) => {
                    return ((new Date(a.created)).getTime() - (new Date(b.created)).getTime());
                }).map((team) => {
                    sum++;
                    const x = ((new Date(team.created)).getTime() - begin.getTime()) / (1000 * 60 * 60 * 24);
                    minTime = minTime < x ? minTime : x;
                    return {
                        color: null,
                        xValue: x,
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

        const legend = () => {
            return <div className="legend">
                {lineChartData.map((datum, key) => {
                    return <div
                        key={key}
                        className="list-group-item"
                        style={{color: datum.color}}>{datum.name}</div>;
                })}
            </div>;
        };

        return <ChartContainer
            chart={LineChart}
            chartProps={{
                data: lineChartData,
                display: {
                    xGrid: true,
                    yGrid: true,
                },
                xScale,
                yScale,

            }}
            legendProps={{data: lineChartData}}
            legendComponent={LineChartLegend}
            headline={translator.getText('Time progress')}
        />;
    }
}
