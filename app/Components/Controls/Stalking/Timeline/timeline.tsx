import { axisBottom } from 'd3-axis';
import { scaleTime, ScaleTime } from 'd3-scale';
import { select } from 'd3-selection';
import { ChartComponent } from 'FKSDB/Components/Charts/Core/chart-component';
import * as React from 'react';
import './timeline.scss';
import { Translator } from '@translator/translator';

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
    translator: Translator;
}

export default function Timeline(props: Props) {
    const lineHeight = 30;
    let rowNumber;

    const rectProperty = {
        rx: 7.5,
        ry: 7.5,
        height: 15,
    };
    const createTimeXScale = (start: Date, end: Date): ScaleTime<number, number> => {
        return scaleTime<number>().domain([start, end]).range(ChartComponent.getInnerXSize());
    }

    const getCurrentY = (): number => {
        return rowNumber * lineHeight;
    }

    const createEvents = (data: Array<{ event: Event }>, label: string) => {
        return <g transform={'translate(0,' + getCurrentY() + ')'}>
            {data.length && (rowNumber += 1) && data.map((datum, index) => {
                const cx = scale(new Date(datum.event.begin));
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

    const createContestants = (contestants: Contestant[]) => {
        const contests = {};
        contestants.forEach((contestant, index) => {
            contests[contestant.model.contestId] = contests[contestant.model.contestId] || [];
            createRect(index, contestant.model.contestId, 'Contestant #' + contestant.model.contestantId, contestant.since, contestant.until)
            contests[contestant.model.contestId].push(createRect(
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
                rowNumber += 1;
                finalRows.push(<g key={id} transform={'translate(0,' + getCurrentY() + ')'}>
                    {contests[id]}
                </g>);
            }
        }
        return finalRows;
    }

    const createRect = (index: number, contestId: number, label: string, since: string, until: string): JSX.Element => {
        const sinceDate = scale(new Date(since));
        const untilDate = scale(new Date(until));
        return <g
            key={index}
            style={{'--color': 'var(--color-contest-' + contestId + ')'} as React.CSSProperties}
        >
            <rect
                x={sinceDate}
                width={untilDate - sinceDate}
                {...rectProperty}
            >
                <title>{label}</title>
            </rect>
            <text
                y={rectProperty.height / 2}
                x={(sinceDate + untilDate) / 2}
            >{label}</text>
        </g>;
    }

    rowNumber = 0;
    const {
        events: {eventOrgs, eventParticipants, eventTeachers},
        states: {orgs, contestants},
        scale: {max, min},
    } = props.data;
    const scale = createTimeXScale(new Date(min), new Date(max));
    const content = <g transform="translate(0,15)">
        {createEvents(eventOrgs, 'Event org')}
        {createEvents(eventParticipants, 'Event participant')}
        {createEvents(eventTeachers, 'Event teacher')}

        {orgs.length && orgs.map((org, index) => {
            rowNumber += 1;
            const y = (rowNumber * lineHeight);
            return <g transform={'translate(0,' + y + ')'} key={index}>
                {createRect(
                    index,
                    org.model.contestId,
                    'Org #' + org.model.orgId,
                    org.since,
                    org.until,
                )}
            </g>;
        })}
        {createContestants(contestants)}
    </g>;
    rowNumber += 3;
    return <div className="person-detail-timeline">
        <svg className="chart"
             style={{'--line-height': lineHeight} as React.CSSProperties}
             viewBox={'0 0 ' + ChartComponent.size.width + ' ' + getCurrentY()}>
            <g transform={'translate(0,' + (getCurrentY() - ChartComponent.margin.bottom) + ')'}
               className="axis x-axis grid"
               ref={(xAxisRef) => {
                   const xAxis = axisBottom(scale).tickSizeInner(-getCurrentY());
                   select(xAxisRef).call(xAxis);
               }}/>
            {content}
        </svg>
    </div>;

}
