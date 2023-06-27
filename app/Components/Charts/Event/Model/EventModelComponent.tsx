import * as React from 'react';
import ChartContainer from 'FKSDB/Components/Charts/Core/ChartContainer';
import Chart, { Link as SimLink, Node as SimNode } from './Chart';
import { scaleOrdinal } from 'd3-scale';
import { schemeCategory10 } from 'd3-scale-chromatic';
import { availableLanguage, Translator } from '@translator/translator';

export interface Link {
    from: string;
    to: string;
    label: string;
}

export interface Node {
    label: string;
    type: 'init' | 'terminated' | 'default';
}

interface OwnProps {
    data: {
        links: Link[];
        nodes: {
            [key: number]: Node;
        };
    };
    translator: Translator<availableLanguage>;
}

export default class EventModelComponent extends React.Component<OwnProps, never> {

    public render() {
        const {data: {links, nodes}} = this.props;
        const simNodes: {
            [key: number]: SimNode;
        } = {};
        const color = scaleOrdinal(schemeCategory10);
        for (const key in nodes) {
            if (nodes.hasOwnProperty(key)) {
                simNodes[key] = {
                    label: nodes[key].label,
                    color: color(key),
                };
            }
        }
        const simLinks = links.map<SimLink>((link): SimLink => {
            return {
                label: link.label,
                color: '#ccc',
                type: 'one-way',
                source: simNodes[link.from],
                target: simNodes[link.to],
            };
        });
        return <ChartContainer
            chart={Chart}
            chartProps={{links: simLinks, nodes: Object.values(simNodes), colors: ['#ccc']}}
        />;
    }
}
