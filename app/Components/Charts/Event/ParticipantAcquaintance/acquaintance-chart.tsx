import { Chord, chord, ChordGroup, ribbon } from 'd3-chord';
import { scaleOrdinal } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import { arc } from 'd3-shape';
import * as React from 'react';
import { useState } from 'react';
import './style.scss';
import { availableLanguage, Translator } from '@translator/translator';

export interface Data {
    person: {
        name: string;
        gender: 'M' | 'F';
    };
    participation: number[];
}

export interface OwnProps {
    data: Data[];
    translator: Translator<availableLanguage>;
}

export default function AcquaintanceChart({data}: OwnProps) {
    const innerRadius = 320;
    const outerRadius = 340;
    const textRadius = 360;
    const [activeId, setActiveId] = useState<number | null>(null);
    const matrix = [];
    data.forEach((personA, indexA) => {
        matrix[indexA] = [];
        data.forEach((personB, indexB) => {
            if (personB.person === personA.person) {
                matrix[indexA][indexB] = 0;
                return;
            }
            if (activeId !== null && (indexA !== activeId && indexB !== activeId)) {
                matrix[indexA][indexB] = 0;
                return;
            }
            matrix[indexA][indexB] = personA.participation.reduce((count, eventId) => {
                if (personB.participation.indexOf(eventId) !== -1) {
                    return count + 1;
                }
                return count;
            }, 0);
        });
    });
    const layout = chord().padAngle(0.05)(matrix);

    const colorScale = scaleOrdinal(schemeCategory10);
    const ribbonCreator = ribbon<Chord, string>().radius(innerRadius);

    const chords = layout.map((datum, index) => {
        let className = 'default';
        if (activeId !== null) {
            className = 'inactive';
        }
        if (datum.source.index === activeId || datum.target.index === activeId) {
            className = 'active';
        }
        // @ts-ignore
        const dAttr: string = ribbonCreator(datum);
        return <path
            key={index}
            className={'ribbon ' + className}
            d={dAttr}
            style={{'--color': colorScale(datum.source.index + '-' + datum.target.index)} as React.CSSProperties}
        />;
    });
    const textArc = arc<ChordGroup>().innerRadius(textRadius).outerRadius(textRadius);
    const labels = layout.groups.map((datum, index) => {
        const angle = ((datum.startAngle + datum.endAngle) / 2);
        const isOther = angle < Math.PI;
        let count = null;
        const isActive = activeId !== null && activeId === datum.index;
        if (activeId !== null) {
            if (activeId !== datum.index) {
                count = matrix[datum.index][activeId];
            }
        } else {
            count = datum.value;
        }

        return <g
            key={index}
            transform={'translate(' + textArc.centroid(datum).join(',') + ')'}
        >
            <text
                className={'label' + (isActive ? ' active' : '') + (isOther ? ' other' : '')}
                transform={'rotate(' + ((isOther ? (angle - Math.PI / 2) : angle + Math.PI / 2) * 180 / Math.PI) + ')'}
            >{data[index].person.name}
                {count !== null ? (' (' + count + ')') : null}</text>
        </g>;
    });
    const arcGenerator = arc<ChordGroup>()
        .innerRadius(innerRadius)
        .outerRadius(outerRadius);

    const arcs = layout.groups.map((datum, index) => {
        let className = '';
        if (activeId !== null && datum.index === activeId) {
            className = 'active';
        }

        return <path
            key={index}
            className={'arc ' + className}
            d={arcGenerator(datum)}
            style={{'--color': data[index].person.gender === 'M' ? 'blue' : 'deeppink'} as React.CSSProperties}
            onClick={() => setActiveId(activeId === index ? null : index)}/>;
    });

    return <div className="chart-participant-acquaintance">
        <svg viewBox="0 0 1200 1200" className="chart">
            <g transform="translate(600,600)">
                <g className="chords">
                    {chords}
                </g>
                <g className="albes">
                    {labels}
                </g>
                <g className="arcs">
                    {arcs}
                </g>
            </g>
        </svg>
    </div>;
}
