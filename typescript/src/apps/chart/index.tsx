import { App, NetteActions } from '@appsCollector';
import { Chord, chord, ChordGroup, Chords, ribbon } from 'd3-chord';
import { scaleOrdinal } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import { arc } from 'd3-shape';
import { ascending } from 'd3-array';
import * as React from 'react';
import * as ReactDOM from 'react-dom';

export const charts: App = (element: Element, module: string, component: string, mode: string, rawData: string, actions: NetteActions) => {
    const container = document.querySelector('.container');
    container.classList.remove('container');
    container.classList.add('container-fluid');
    if (module === 'chart') {
        ReactDOM.render(<Index data={JSON.parse(rawData)}/>, element);
        return true;
    }
    return false;

};

interface Data {
    person: {
        name: string;
        gender: 'M' | 'F';
    };
    participation: number[];
}

class Index extends React.Component<{ data: Data[] }, { activeId: number }> {
    private innerRadius = 150;
    private outerRadius = 170;
    private textRadius = 180;

    constructor(props) {
        super(props);
        this.state = {activeId: null};
    }

    public render() {

        const matrix = this.calculateData();

        const layout = chord().padAngle(0.05)(matrix);
        const arcGenerator = arc<ChordGroup>()
            .innerRadius(this.innerRadius)
            .outerRadius(this.outerRadius);
        const paths = [];
        const textArc = arc<ChordGroup>().innerRadius(this.textRadius).outerRadius(this.textRadius);
        const unrendered = [];
        layout.groups.forEach((datum, index) => {
            if (datum.value === 0) {
                unrendered.push(datum);
                return;
            }

            const angle = ((datum.startAngle + datum.endAngle) / 2);
            const isOther = angle < Math.PI;
            paths.push(<g transform={'translate(' + textArc.centroid(datum).join(',') + ')'}>
                <text
                    transform={'rotate(' + ((isOther ? (angle - Math.PI / 2) : angle + Math.PI / 2) * 180 / Math.PI) + ')'}
                    textAnchor={isOther ? 'start' : 'end'}
                    alignmentBaseline="central"
                    fontSize="0.8rem"
                >{this.getPerson(index).person.name}</text>
            </g>);
            paths.push(<path
                d={arcGenerator(datum)}
                fill={this.getPerson(index).person.gender === 'M' ? 'blue' : 'pink'}
                onMouseEnter={() => {
                    this.setState({activeId: index});
                }}
                onMouseLeave={() => {
                    this.setState({activeId: null});
                }}/>);
        });

        return <>
            <svg viewBox="0 0 800 600" className="chart">
                <g transform="translate(400,300)">
                    {this.getChord(layout)}
                    {paths}</g>
            </svg>
            <div>
                {unrendered.map((datum) => {
                    return <span className="row">{this.getPerson(datum.index).person.name}</span>;
                })}
            </div>
            <div>
                {matrix.map((datum, index) => {
                    return <>
                        <span>{this.getPerson(index).person.name}</span>
                        <ul>{
                            datum.map((datum2, index2) => {
                                return <li>{this.getPerson(index2).person.name} ({datum2})</li>;
                            })}
                        </ul>
                    </>;
                })}
            </div>
        </>;
    }

    private getPerson(index: number): Data {
        const {data} = this.props;
        return data[index];
    }

    private getChord(layout: Chords): JSX.Element {
        const colorScale = scaleOrdinal(schemeCategory10);
        const ribbonCreator = ribbon<Chord, string>().radius(150);
        return <>
            {layout.map((datum) => {
                let opacity = 0.2;
                if (this.state.activeId !== null) {
                    opacity = 0.1;
                }
                if (datum.source.index === this.state.activeId || datum.target.index === this.state.activeId) {
                    opacity = 1;
                }
                return <path d={ribbonCreator(datum)}
                             fill={colorScale(datum.source.index + '-' + datum.source.subindex)}
                             opacity={opacity}/>;
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
