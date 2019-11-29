import AbstractChart from '@shared/components/chart';
import {
    axisBottom,
    axisLeft,
} from 'd3-axis';
import {
    ScaleLinear,
    scaleLinear,
    scaleOrdinal,
} from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import { select } from 'd3-selection';
import {
    curveBasis,
    line,
} from 'd3-shape';
import * as React from 'react';
import { Data } from './index';

interface OwnProps {
    data: Data;
}

export default class Chart extends AbstractChart<OwnProps, {}> {

    private xAxis: SVGGElement;
    private yAxis: SVGGElement;

    private xScale: ScaleLinear<number, number>;
    private yScale: ScaleLinear<number, number>;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const {data} = this.props;

        let minTime = 0;
        let max = 0;
        const eventsData = {};
        const legends = [];

        const colorScale = scaleOrdinal(schemeCategory10);
        for (const eventId in data.teams) {
            if (data.teams.hasOwnProperty(eventId) && data.events.hasOwnProperty(eventId)) {
                const event = data.events[eventId];
                const teams = data.teams[eventId];

                const begin = new Date(event.begin);
                let sum = 0;
                legends.push(<div key={eventId}
                                  className={'list-group-item'}
                                  style={{color: colorScale(eventId)}}>{event.name}</div>);
                const eventData = teams.sort((a, b) => {
                    return ((new Date(a.created)).getTime() - (new Date(b.created)).getTime());
                }).map((team) => {
                    sum++;
                    const x = ((new Date(team.created)).getTime() - begin.getTime()) / (1000 * 60 * 60 * 24);
                    minTime = minTime < x ? minTime : x;
                    return {
                        x,
                        y: sum,
                    };
                });
                max = max > eventData.length ? max : eventData.length;
                eventsData[eventId] = eventData;
            }
        }

        this.yScale = scaleLinear<number, number>().domain([0, max]).range(this.getInnerYSize());
        this.xScale = scaleLinear<number, number>().domain([minTime, 0]).range(this.getInnerXSize());

        interface Item {
            x: number;
            y: number;
        }

        const dots = [];
        const lines = [];
        for (const index in eventsData) {
            if (eventsData.hasOwnProperty(index)) {

                const lineEl = line<Item>()
                    .x((element: Item) => {
                        return this.xScale(element.x);
                    })
                    .y((element: Item) => {
                        return this.yScale(element.y);
                    })
                    .curve(curveBasis)(eventsData[index]);
                lines.push(<path key={index} d={lineEl} className={'line'} stroke={colorScale(index)}/>);
            }
        }

        return (
            <div className={'row'}>
                <div className={'col-8'}>
                    <svg viewBox={this.getViewBox()} className="chart time-line-histogram">
                        <g>
                            {lines}
                            {dots}
                            <g transform={this.transformXAxis()}
                               className="x-axis"
                               ref={(xAxis) => this.xAxis = xAxis}/>
                            <g transform={this.transformYAxis()} className="y-axis"
                               ref={(yAxis) => this.yAxis = yAxis}/>
                        </g>
                    </svg>
                </div>
                <div className={'col-4'}>
                    <div className={'list-group'}>
                        {legends}
                    </div>
                </div>
            </div>
        );
    }

    private getAxis(): void {
        const xAxis = axisBottom<number>(this.xScale);
        select(this.xAxis).call(xAxis);

        const yAxis = axisLeft<number>(this.yScale);
        select(this.yAxis).call(yAxis);
    }


}
