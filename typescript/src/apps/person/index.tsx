import { mapRegister } from '@appsCollector';
import AbstractChart from '@shared/components/chart';
import { axisBottom } from 'd3-axis';
import { scaleOrdinal, ScaleTime } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import { select } from 'd3-selection';
import * as React from 'react';
import * as ReactDOM from 'react-dom';

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
}

class DetailTimeline extends AbstractChart<Props, {}> {
    private colorScale;
    private readonly lineHeight = 30;
    private rowNumber;
    private scale: ScaleTime<number, number>;

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
        const {events: {eventOrgs, eventParticipants, eventTeachers}, scale: {max, min}, states: {orgs, contestants}} = this.props.data;
        this.scale = this.createTimeXScale(new Date(min), new Date(max));
        const content = <g transform="translate(0,15)" className="content">
            {this.createEvents(eventOrgs, 'Event org')}
            {this.createEvents(eventParticipants, 'Event participant')}
            {this.createEvents(eventTeachers, 'Event teacher')}

            {orgs.length && orgs.map((org, index) => {
                const since = this.scale(new Date(org.since));
                const until = this.scale(new Date(org.until));
                const y = (this.rowNumber * this.lineHeight);
                this.rowNumber += 1;
                return <g key={index} transform={'translate(0,' + y + ')'}
                          data-contest-id={org.model.contestId}>
                    <rect x={since}
                          width={until - since}
                          height={this.lineHeight}>
                        <title>''</title>
                    </rect>
                    <text y={this.lineHeight / 2}
                          x={(since + until) / 2}>Org #{org.model.orgId}</text>
                </g>;
            })}
            {this.createContestants(contestants)}
        </g>;
        this.rowNumber += 3;
        return <div className="row-12">
            <svg className="w-100 chart person-timeline" viewBox={'0 0 ' + this.size.width + ' ' + this.getCurrentY()}>
                <g transform={'translate(0,' + (this.getCurrentY() - this.margin.bottom) + ')'}
                   className={'axis x-axis grid'}
                   ref={(xAxis) => this.xAxis = xAxis}/>
                {content}
            </svg>
        </div>;

    }

    private getCurrentY(): number {
        return this.rowNumber * this.lineHeight;
    }

    private createEvents(data: Array<{ event: Event }>, label: string) {
        return <g transform={'translate(0,' + this.getCurrentY() + ')'}>
            {data.length && (this.rowNumber += 1) && data.map((datum, index) => {
                const cx = this.scale(new Date(datum.event.begin));
                return <circle data-contest-id={datum.event.contestId}
                               data-event-type-id={datum.event.eventTypeId}
                               key={index} r="7.5" cx={cx}>
                    <title>{label} @ {datum.event.name}</title>
                </circle>;
            })}
        </g>;
    }

    private createContestants(contestants: Contestant[]) {
        const contests = {};
        contestants.forEach((contestant, index) => {
            const since = this.scale(new Date(contestant.since));
            const until = this.scale(new Date(contestant.until));
            contests[contestant.model.contestId] = contests[contestant.model.contestId] || [];
            contests[contestant.model.contestId].push(<g key={index}
                                                         data-contest-id={contestant.model.contestId}>
                <rect x={since}
                      width={until - since}
                      height={this.lineHeight}>
                    <title>Contestant #{contestant.model.contestantId}</title>
                </rect>
                <text y={this.lineHeight / 2}
                      x={(since + until) / 2}>C. #{contestant.model.contestantId}</text>
            </g>);
        });
        const finalRows = [];
        for (const id in contests) {
            if (contests.hasOwnProperty(id)) {
                this.rowNumber += 1;
                finalRows.push(<g key={id} transform={'translate(0,' + this.getCurrentY() + ')'}>
                    {contests[id]}
                </g>);
            }
        }
        return finalRows;
    }

    private getAxis(): void {
        const xAxis = axisBottom(this.scale).tickSizeInner(-this.getCurrentY());
        select(this.xAxis).call(xAxis);
    }
}

export const person = () => {
    mapRegister.register('person.detail.timeline', (element, reactId, rawData, actions) => {
        const c = document.createElement('div');
        element.appendChild(c);
        const data = JSON.parse(rawData);
        ReactDOM.render(<DetailTimeline data={data}/>, c);
    });
};
