import {
    forceLink,
    forceManyBody,
    forceSimulation,
    forceX,
    forceY,
    SimulationLinkDatum,
    SimulationNodeDatum,
} from 'd3-force';
import * as React from 'react';
import ChartComponent from 'FKSDB/Components/Charts/Core/chart-component';
import './node-chart.scss';

export interface Link extends SimulationLinkDatum<Node> {
    type: 'one-way' | 'bi-dir';
    line?: 'solid' | 'dashed';
    label: string;
    color: string;
}

export interface Node extends SimulationNodeDatum {
    label: string;
    color: string;
}

interface OwnProps {
    links: Link[];
    nodes: Node[];
    colors: string[];
}

export default class NodeChart extends ChartComponent<OwnProps, Record<string, never>> {
    private simulation = null;

    public componentDidMount() {
        this.simulation.on('tick', () => {
            this.forceUpdate();
        });
    }

    public render() {
        const {links, nodes} = this.props;
        this.simulation = forceSimulation<Node>(nodes)
            .force('link', forceLink(links))
            .force('charge', forceManyBody().strength(-1000))
            .force('x', forceX())
            .force('y', forceY())
            .alphaMin(0.001);

        const nodesElements = [];

        for (const key in nodes) {
            if (Object.hasOwn(nodes,key)) {
                const node = nodes[key];
                nodesElements.push(
                    <g fill="currentColor"
                       style={{'--color': node.color} as React.CSSProperties}
                       key={key}
                       onClick={() => {
                           if (node.fx === 0) {
                               node.fx = null;
                               node.fy = null;
                           } else {
                               node.fx = 0;
                               node.fy = 0;
                           }
                       }}
                       transform={'translate(' + node.x + ',' + node.y + ')'}>
                        <circle
                            r="7.5"
                        />
                        <text x="8" y="0.31rem">
                            {node.label}
                        </text>
                    </g>,
                );
            }
        }
        return <div className="node-chart">
            <svg
                className="chart"
                viewBox={`-${this.size.width / 2} -${this.size.height / 2} ${this.size.width} ${this.size.height}`}>
                <defs>
                    {this.props.colors.map((color) => {
                        return <marker
                            style={{'--marker-color': color} as React.CSSProperties}
                            key={color}
                            viewBox="-5 0 10 10"
                            id={'arrow-end'}
                            refX="10"
                            refY="5"
                            markerWidth="10"
                            markerHeight="10"
                            orient="auto"
                        >
                            <path d="M 5 5 L -2 2 L -2 8 z"/>
                        </marker>;
                })}
            </defs>
            <g className="links">{links.map((item, index) => {
                let path;
                const source = item.source as Node;
                const target = item.target as Node;
                if (item.type === 'bi-dir') {
                    path = <path
                        d={`M ${source.x} ${source.y} L ${target.x} ${target.y}`}
                        markerEnd={`url(#arrow-end)`}
                        markerStart={`url(#arrow-start)`}/>;
                } else {
                    const r = Math.hypot(target.x - source.x, target.y - source.y);
                    const rot = Math.atan((target.y - source.y) / (target.x - source.x)) * 180 / Math.PI;
                    path = <path
                        d={`M ${source.x} ${source.y} A ${r} ${r} ${rot} 0 1 ${target.x} ${target.y}`}
                        markerEnd={`url(#arrow-end)`}/>;
                }
                return <g key={index} style={{
                    '--color': item.color,
                    '--line': item.line === 'dashed' ? '5,5' : 'none',
                } as React.CSSProperties}>
                    {path}
                </g>;

            })}</g>
                <g className="nodes">
                    {nodesElements}
                </g>
            </svg>
        </div>;
    }
}
