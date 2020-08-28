import {
    Chord,
    chord,
    ChordGroup,
    Chords,
    ribbon,
} from 'd3-chord';
import { scaleOrdinal } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import { arc } from 'd3-shape';
import * as React from 'react';

interface Data {
    person: {
        name: string;
        gender: 'M' | 'F';
    };
    participation: number[];
}

interface State {
    activeId: number;
}

interface OwnProps {
    data: Data[];
}

export default class ParticipantAcquaintance extends React.Component<OwnProps, State> {
    private readonly innerRadius = 320;
    private readonly outerRadius = 340;
    private readonly textRadius = 360;

    constructor(props) {
        super(props);
        this.state = {activeId: null};
    }

    public render() {
        const matrix = this.calculateData();
        const layout = chord().padAngle(0.05)(matrix);
        return <div className="row">
            <div className="chart-container col-lg-8 offset-lg-2">
                <svg viewBox="0 0 1200 1200" className="chart chart-participant-acquaintance">
                    <g transform="translate(600,600)">
                        {this.getChord(layout)}
                        {this.getLabels(matrix, layout.groups)}
                        {this.getArc(layout.groups)}
                    </g>
                </svg>
            </div>
        </div>;
    }

    private getArc(groups: ChordGroup[]): JSX.Element {
        const arcGenerator = arc<ChordGroup>()
            .innerRadius(this.innerRadius)
            .outerRadius(this.outerRadius);

        return <>{groups.map((datum, index) => {
            let className = 'inactive';
            if (this.state.activeId !== null && datum.index === this.state.activeId) {
                className = 'active';
            }

            return <path
                className={'arc ' + className}
                d={arcGenerator(datum)}
                cursor={'pointer'}
                fill={this.getPerson(index).person.gender === 'M' ? 'blue' : 'deeppink'}
                onClick={() => {
                    this.setState({activeId: this.state.activeId === index ? null : index});
                }}/>;
        })
        }</>;
    }

    private getLabels(matrix: number[][], groups: ChordGroup[]): JSX.Element {
        const textArc = arc<ChordGroup>().innerRadius(this.textRadius).outerRadius(this.textRadius);
        const {activeId} = this.state;
        return <>{groups.map((datum, index) => {
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

            return <g transform={'translate(' + textArc.centroid(datum).join(',') + ')'}>
                <text
                    transform={'rotate(' + ((isOther ? (angle - Math.PI / 2) : angle + Math.PI / 2) * 180 / Math.PI) + ')'}
                    textAnchor={isOther ? 'start' : 'end'}
                    alignmentBaseline="central"
                    fontWeight={isActive ? 'bold' : ''}
                >{this.getPerson(index).person.name}
                    {count !== null ? (' (' + count + ')') : null}</text>
            </g>;
        })}</>;

    }

    private getPerson(index: number): Data {
        const {data} = this.props;
        return data[index];
    }

    private getChord(layout: Chords): JSX.Element {
        const colorScale = scaleOrdinal(schemeCategory10);
        const ribbonCreator = ribbon<Chord, string>().radius(this.innerRadius);
        return <>
            {layout.map((datum) => {
                let className = 'default';
                if (this.state.activeId !== null) {
                    className = 'inactive';
                }
                if (datum.source.index === this.state.activeId || datum.target.index === this.state.activeId) {
                    className = 'active';
                }
                // @ts-ignore: Type 'void' is not assignable to type 'string'.
                const dAttr: string = ribbonCreator(datum);
                return <path
                    className={'ribbon ' + className}
                    d={dAttr}
                    fill={colorScale(datum.source.index + '-' + datum.source.subindex)}
                />;
            })}
        </>;
    }

    private calculateData(): number[][] {
        const {data} = this.props;
        const {activeId} = this.state;
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
        return matrix;
    }
}
