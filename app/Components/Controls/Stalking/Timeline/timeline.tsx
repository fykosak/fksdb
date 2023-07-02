import { axisBottom } from 'd3-axis';
import { scaleOrdinal, scaleTime, ScaleTime } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import { select } from 'd3-selection';
import ChartComponent from 'FKSDB/Components/Charts/Core/chart-component';
import * as React from 'react';
import './timeline.scss';
import { availableLanguage, Translator } from '@translator/translator';

interface Event {
    begin: string;
    eventId: number;
    contestId: 1 | 2;
    eventTypeId: number;
    name: string;
}

interface State {
    since: string;
    until: string;
}

interface Contestant extends State {
    model: {
        contestantId: number;
        contestId: 1 | 2;
    };
}

interface Org extends State {
    model: {
        orgId: number;
        contestId: 1 | 2;
    };
}

interface Props {
    data: {
        events: {
            eventOrgs: Array<{
                event: Event;
                model: null;
            }>;
            eventParticipants: Array<{
                event: Event;
                model: null;
            }>;
            eventTeachers: Array<{
                event: Event;
                model: null;
            }>;
        };
        scale: {
            max: string;
            min: string;
        };
        states: {
            contestants: Contestant[];
            orgs: Org[];
        };
    };
    translator: Translator<availableLanguage>;
}

export default class Timeline extends ChartComponent<Props, Record<string, never>> {
    private colorScale;
    private readonly lineHeight = 30;
    private rowNumber;
    private scale: ScaleTime<number, number>;

    private readonly rectProperty = {
        rx: 7.5,
        ry: 7.5,
        height: 15,
    };

    private xAxis: SVGGElement;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        this.colorScale = scaleOrdinal(schemeCategory10);
        this.rowNumber = 0;
        const {
            events: {eventOrgs, eventParticipants, eventTeachers},
            states: {orgs, contestants},
            scale: {max, min},
        } = this.props.data;
        this.scale = this.createTimeXScale(new Date(min), new Date(max));
        const content = <g transform="translate(0,15)">
            {this.createEvents(eventOrgs, 'Event org')}
            {this.createEvents(eventParticipants, 'Event participant')}
            {this.createEvents(eventTeachers, 'Event teacher')}

            {orgs.length && orgs.map((org, index) => {
                this.rowNumber += 1;
                const y = (this.rowNumber * this.lineHeight);
                return <g transform={'translate(0,' + y + ')'}>{this.createRect(
                    index,
                    org.model.contestId,
                    'Org #' + org.model.orgId,
                    org.since,
                    org.until,
                )}</g>;
            })}
            {this.createContestants(contestants)}
        </g>;
        this.rowNumber += 3;
        return <div className="person-detail-timeline">
            <svg className="chart"
                 style={{'--line-height': this.lineHeight} as React.CSSProperties}
                 viewBox={'0 0 ' + this.size.width + ' ' + this.getCurrentY()}>
                <g transform={'translate(0,' + (this.getCurrentY() - this.margin.bottom) + ')'}
                   className="axis x-axis grid"
                   ref={(xAxis) => this.xAxis = xAxis}/>
                {content}
            </svg>
        </div>;
    }

    private createTimeXScale(start: Date, end: Date): ScaleTime<number, number> {
        return scaleTime<number>().domain([start, end]).range(this.getInnerXSize());
    }

    private getCurrentY(): number {
        return this.rowNumber * this.lineHeight;
    }

    private createEvents(data: Array<{ event: Event }>, label: string) {
        return <g transform={'translate(0,' + this.getCurrentY() + ')'}>
            {data.length && (this.rowNumber += 1) && data.map((datum, index) => {
                const cx = this.scale(new Date(datum.event.begin));
                return <circle
                    style={{'--color': 'var(--color-event-type-' + datum.event.eventTypeId + ')'} as React.CSSProperties}
                    key={index}
                    r="7.5"
                    cx={cx}
                >
                    <title>{label} @ {datum.event.name}</title>
                </circle>;
            })}
        </g>;
    }

    private createContestants(contestants: Contestant[]) {
        const contests = {};
        contestants.forEach((contestant, index) => {
            contests[contestant.model.contestId] = contests[contestant.model.contestId] || [];
            this.createRect(index, contestant.model.contestId, 'Contestant #' + contestant.model.contestantId, contestant.since, contestant.until)
            contests[contestant.model.contestId].push(this.createRect(
                index,
                contestant.model.contestId,
                'Contestant #' + contestant.model.contestantId,
                contestant.since,
                contestant.until,
            ));
        });
        const finalRows = [];
        for (const id in contests) {
            if (Object.hasOwn(contests, id)) {
                this.rowNumber += 1;
                finalRows.push(<g key={id} transform={'translate(0,' + this.getCurrentY() + ')'}>
                    {contests[id]}
                </g>);
            }
        }
        return finalRows;
    }

    private createRect(index: number, contestId: number, label: string, since: string, until: string): JSX.Element {
        const sinceDate = this.scale(new Date(since));
        const untilDate = this.scale(new Date(until));
        return <g
            key={index}
            style={{'--color': 'var(--color-contest-' + contestId + ')'} as React.CSSProperties}
        >
            <rect
                x={sinceDate}
                width={untilDate - sinceDate}
                {...this.rectProperty}
            >
                <title>{label}</title>
            </rect>
            <text
                y={this.rectProperty.height / 2}
                x={(sinceDate + untilDate) / 2}
            >{label}</text>
        </g>;
    }

    private getAxis(): void {
        const xAxis = axisBottom(this.scale).tickSizeInner(-this.getCurrentY());
        select(this.xAxis).call(xAxis);
    }
}
