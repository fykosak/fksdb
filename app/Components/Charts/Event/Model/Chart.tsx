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
import ChartComponent from 'FKSDB/Components/Charts/Core/ChartComponent';

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

export default class Chart extends ChartComponent<OwnProps, Record<string, never>> {
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
            if (nodes.hasOwnProperty(key)) {
                const node = nodes[key];
                nodesElements.push(
                    <g fill="currentColor"
                       key={key}
                       strokeLinecap="round"
                       onClick={() => {
                           if (node.fx === 0) {
                               node.fx = null;
                               node.fy = null;
                           } else {
                               node.fx = 0;
                               node.fy = 0;
                           }
                       }}
                       strokeLinejoin="round"
                       transform={'translate(' + node.x + ',' + node.y + ')'}>
                        <circle
                            stroke="none"
                            fill={node.color}
                            r="7.5"
                        />
                        <text x="8" y="0.31rem" fontSize=".5rem" fontWeight="bold" fill={node.color}>
                            {node.label}
                        </text>
                    </g>,
                );
            }
        }
        return <svg
            viewBox={`-${this.size.width / 2} -${this.size.height / 2} ${this.size.width} ${this.size.height}`}>
            <defs>
                {this.props.colors.map((color) => {
                    return <marker
                        key={color}
                        viewBox="-5 0 10 10"
                        id={'arrow-end-' + color.slice(1)}
                        refX="10"
                        refY="5"
                        markerWidth="10"
                        markerHeight="10"
                        orient="auto"
                    >
                        <path
                            d="M 5 5 L -2 2 L -2 8 z"
                            fill={color}
                            stroke="none"
                        />
                    </marker>;
                })}
            </defs>
            <g fill="none" strokeWidth="1.5" className="links">{links.map((item, index) => {
                let path;
                const markKey = item.color.slice(1);
                if (item.type === 'bi-dir') {
                    path = <path
                        stroke={item.color}
                        strokeDasharray={item.line === 'dashed' ? '5,5' : 'none'}
                        // @ts-ignore
                        d={`M ${item.source.x} ${item.source.y} L ${item.target.x} ${item.target.y}`}
                        markerEnd={`url(#arrow-end-${markKey})`}
                        markerStart={`url(#arrow-start-${markKey})`}/>;
                } else {
                    // @ts-ignore
                    const r = Math.hypot(item.target.x - item.source.x, item.target.y - item.source.y);
                    // @ts-ignore
                    const rot = Math.atan((item.target.y - item.source.y) / (item.target.x - item.source.x)) * 180 / Math.PI;
                    path = <path
                        stroke={item.color}
                        strokeDasharray={item.line === 'dashed' ? '5,5' : 'none'}
                        // @ts-ignore
                        d={`M ${item.source.x} ${item.source.y} A ${r} ${r} ${rot} 0 1 ${item.target.x} ${item.target.y}`}
                        markerEnd={`url(#arrow-end-${markKey})`}/>;
                }
                return <g key={index}>
                    {path}
                </g>;

            })}</g>
            <g className="nodes">
                {nodesElements}
            </g>
        </svg>;
    }
}
