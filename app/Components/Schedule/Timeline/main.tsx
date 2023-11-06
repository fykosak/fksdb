import { axisBottom } from 'd3-axis';
import { scaleLinear, scaleOrdinal, scaleTime } from 'd3-scale';
import { select } from 'd3-selection';
import { ChartComponent } from 'FKSDB/Components/Charts/Core/chart-component';
import * as React from 'react';
import { LangMap, Translator } from '@translator/translator';
import { schemeCategory10 } from 'd3-scale-chromatic';

interface ScheduleGroup {
    scheduleGroupId: number;
    scheduleGroupType: string;
    registrationBegin: string;
    registrationEnd: string;
    modificationEnd: string;
    name: LangMap<string, 'cs' | 'en'>;
    eventId: number;
    start: string;
    end: string;
    items: Array<ScheduleItem>;
}

interface ScheduleItem {
    scheduleGroupId: number;
    price: LangMap<string, 'EUR' | 'CZK'>;
    totalCapacity: number;
    usedCapacity: number;
    scheduleItemId: number;
    name: LangMap<string, 'cs' | 'en'>;
    begin: string;
    end: string;
    description: LangMap<string, 'cs' | 'en'>;
    longDescription: LangMap<string, 'cs' | 'en'>;
}

interface Props {
    data: Array<ScheduleGroup>;
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
    const {data} = props;

    const min = Math.min(...data.map((group) => (new Date(group.start)).getTime()));
    const max = Math.max(...data.map((group) => (new Date(group.end)).getTime()));
    const yMax = Math.max(...data.map((group) => (new Date(group.items.length)).getTime()));
    const xScale = scaleTime<number, number>().domain([min, max]).range(ChartComponent.getInnerXSize());
    const yScale = scaleLinear().domain([0, yMax]).range(ChartComponent.getInnerYSize());


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
    const colorScale = scaleOrdinal(schemeCategory10);
    return <div className="person-detail-timeline">
        <svg className="chart"
             style={{'--line-height': lineHeight} as React.CSSProperties}
             viewBox={ChartComponent.getViewBox()}>
            <g transform={ChartComponent.transformXAxis()}
               className="axis x-axis grid"
               ref={(xAxisRef) => {
                   const xAxis = axisBottom(xScale);
                   select(xAxisRef).call(xAxis);
               }}/>
            {data.map((group) => {
                if (group.scheduleGroupType === 'accommodation') {
                    return null;
                }
                const x1 = xScale(new Date(group.start));
                const x2 = xScale(new Date(group.end));
                const y1 = yScale(group.items.length);
                const y2 = yScale(0);
                return <rect key={group.scheduleGroupId}
                             x={x1}
                             y={y1}
                             width={x2 - x1}
                             height={y2 - y1}
                             fill={colorScale(group.scheduleGroupType)}
                             opacity={0.5}
                />
            })
            }
        </svg>
    </div>;

}
