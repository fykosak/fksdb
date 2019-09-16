import { chord, ribbon } from 'd3-chord';
import { Component, default as React } from 'react';
import * as ReactDOM from 'react-dom';
import { App, NetteActions } from '@appsCollector';
import { scaleOrdinal } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import { arc } from 'd3-shape';

export const charts: App = (element: Element, module: string, component: string, mode: string, rawData: string, actions: NetteActions) => {

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

class Index extends Component<{ data: Data[] }, any> {
    constructor(props) {
        super(props);
        this.state = {activeId: null};
    }

    public render() {
        const {data} = this.props;


        const matrix = this.calculateData();

        const layout = chord().padAngle(0.05)(matrix);
        const arcGenerator = arc()
            .innerRadius(150)
            .outerRadius(170);
        const paths = [];
        const ribbonCreator = ribbon().radius(150);
        const colorScale = scaleOrdinal(schemeCategory10);
        layout.forEach((datum) => {
            paths.push(<path d={ribbonCreator(datum)}
                             fill={colorScale(datum.source.index + '-' + datum.source.subindex)}
                             opacity={(datum.source.index === this.state.activeId || datum.target.index === this.state.activeId) ? 1 : 0.1}/>);
        });
        const unrendered = [];
        layout.groups.forEach((datum, index) => {
            if (datum.value === 0) {
                unrendered.push(datum);
                return;
            }
            paths.push(<text
                transform={'rotate(' + (((datum.startAngle + datum.endAngle) / 2) + (Math.PI / 2)) + '),' + 'translate(' + arcGenerator.centroid(datum).join(',') + ')'}>{data[index].person.name}</text>);
            paths.push(<path
                d={arcGenerator(datum)}
                fill={data[index].person.gender === 'M' ? 'blue' : 'pink'}
                onMouseEnter={() => {
                    this.setState({activeId: index});
                }}
                onMouseLeave={() => {
                    this.setState({activeId: null});
                }}/>);
        });

        return <>
            <svg viewBox="0 0 600 400" className="chart">
                <g transform="translate(300,200)">{paths}</g>
            </svg>
            <div>
                {unrendered.map((datum) => {
                    return <span className="row">{data[datum.index].person.name}</span>;
                })}
            </div>
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

/*
const width = 480,
    height = 500,
    outerRadius = Math.min(width, height) / 2 - 4,
    innerRadius = outerRadius - 20;

const format = d3.format(',.3r');

// Square matrices, asynchronously loaded; credits is the transpose of debits.
const debits = [],
    credits = [];

// The chord layout, for computing the angles of chords and groups.
const layout = chord()
    .sortGroups(d3.descending)
    .sortSubgroups(d3.descending)
    .sortChords(d3.descending)
    .padding(0.04);

// The color scale, for different categories of “worrisome” risk.
const fill = d3.scale.ordinal()
    .domain([0, 1, 2])
    .range(['#DB704D', '#D2D0C6', '#ECD08D', '#F8EDD3']);

// The arc generator, for the groups.
const arc = d3.svg.arc()
    .innerRadius(innerRadius)
    .outerRadius(outerRadius);

// The chord generator (quadratic Bézier), for the chords.
const chord = d3.svg.chord()
    .radius(innerRadius);

// Add an SVG element for each diagram, and translate the origin to the center.
const svg = d3.select('body').selectAll('div')
    .data([debits, credits])
    .enter().append('div')
    .style('display', 'inline-block')
    .style('width', width + 'px')
    .style('height', height + 'px')
    .append('svg')
    .attr('width', width)
    .attr('height', height)
    .append('g')
    .attr('transform', 'translate(' + width / 2 + ',' + height / 2 + ')');

// Load our data file…
d3.csv('debt.csv', type, (error, data) => {
    if (error) {
        throw error;
    }

    const countryByName = d3.map();
    let countryIndex = -1;
    const countryByIndex = [];

    // Compute a unique index for each country.
    data.forEach((d) => {
        if (countryByName.has(d.creditor)) {
            d.creditor = countryByName.get(d.creditor);
        } else {
            countryByName.set(d.creditor, d.creditor = {name: d.creditor, index: countryIndex += 1});
        }
        if (countryByName.has(d.debtor)) {
            d.debtor = countryByName.get(d.debtor);
        } else {
            countryByName.set(d.debtor, d.debtor = {name: d.debtor, index: countryIndex += 1});
        }
        d.debtor.risk = d.risk;
    });

    // Initialize a square matrix of debits and credits.
    for (const i = 0; i <= countryIndex; i++) {
        debits[i] = [];
        credits[i] = [];
        for (const j = 0; j <= countryIndex; j++) {
            debits[i][j] = 0;
            credits[i][j] = 0;
        }
    }

    // Populate the matrices, and stash a map from index to country.
    data.forEach((d) => {
        debits[d.creditor.index][d.debtor.index] = d;
        credits[d.debtor.index][d.creditor.index] = d;
        countryByIndex[d.creditor.index] = d.creditor;
        countryByIndex[d.debtor.index] = d.debtor;
    });

    // For each diagram…
    svg.each((matrix, j) => {
        const svg = d3.select(this);

        // Compute the chord layout.
        layout.matrix(matrix);

        // Add chords.
        svg.selectAll('.chord')
            .data(layout.chords)
            .enter().append('path')
            .attr('class', 'chord')
            .style('fill', (d) => {
                return fill(d.source.value.risk);
            })
            .style('stroke', (d) => {
                return d3.rgb(fill(d.source.value.risk)).darker();
            })
            .attr('d', chord)
            .append('title')
            .text((d) => {
                return d.source.value.debtor.name + ' owes ' + d.source.value.creditor.name + ' $' + format(d.source.value) + 'B.';
            });

        // Add groups.
        const g = svg.selectAll('.group')
            .data(layout.groups)
            .enter().append('g')
            .attr('class', 'group');

        // Add the group arc.
        g.append('path')
            .style('fill', (d) => {
                return fill(countryByIndex[d.index].risk);
            })
            .attr('id', (d, i) => {
                return 'group' + d.index + '-' + j;
            })
            .attr('d', arc)
            .append('title')
            .text((d) => {
                return countryByIndex[d.index].name + ' ' + (j ? 'owes' : 'is owed') + ' $' + format(d.value) + 'B.';
            });

        // Add the group label (but only for large groups, where it will fit).
        // An alternative labeling mechanism would be nice for the small groups.
        g.append('text')
            .attr('x', 6)
            .attr('dy', 15)
            .filter((d) => {
                return d.value > 110;
            })
            .append('textPath')
            .attr('xlink:href', (d) => {
                return '#group' + d.index + '-' + j;
            })
            .text((d) => {
                return countryByIndex[d.index].name;
            });
    });
});

const type = (d) => {
    d.amount = +d.amount;
    d.risk = +d.risk;
    d.valueOf = value; // for chord layout
    return d;
};

const value = () => {
    return this.amount;
};
*/
