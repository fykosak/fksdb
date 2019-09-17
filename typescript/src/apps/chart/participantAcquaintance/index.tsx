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

export default class Index extends React.Component<{ data: Data[] }, { activeId: number }> {
    private innerRadius = 320;
    private outerRadius = 340;
    private textRadius = 360;

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
            let className = 'active';
            if (this.state.activeId !== null) {
                className = 'inactive';
            }
            if (datum.index === this.state.activeId) {
                className = 'active';
            }

            return <path
                className={'arc ' + className}
                d={arcGenerator(datum)}
                fill={this.getPerson(index).person.gender === 'M' ? 'blue' : 'deeppink'}
                onMouseEnter={() => {
                    this.setState({activeId: index});
                }}
                onMouseLeave={() => {
                    this.setState({activeId: null});
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
                return <path
                    className={'ribbon ' + className}
                    d={ribbonCreator(datum)}
                    fill={colorScale(datum.source.index + '-' + datum.source.subindex)}
                />;
            })}
        </>;
    }

    private calculateData(): number[][] {
        const {data} = this.props;
        const matrix = [];
        data.forEach((personA, indexA) => {
            matrix[indexA] = [];
            data.forEach((personB, indexB) => {
                if (personB.person === personA.person) {
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
