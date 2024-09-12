import * as React from 'react';
import NodeChart, {Link as SimLink, Node as SimNode} from '../../Core/NodeChart/node-chart';
import {scaleOrdinal} from 'd3-scale';
import {schemeCategory10} from 'd3-scale-chromatic';
import {Translator} from '@translator/translator';

export interface Link {
    from: string;
    to: string;
    label: string;
    behaviorType: string;
}

export interface Node {
    label: string;
    behaviorType: string;
}

interface OwnProps {
    data: {
        links: Link[];
        nodes: {
            [key: number]: Node;
        };
    };
    translator: Translator;
}

export default function ModelChart({data: {links, nodes}}: OwnProps) {

    const simNodes: {
        [key: number]: SimNode;
    } = {};
    for (const key in nodes) {
        if (Object.hasOwn(nodes, key)) {
            simNodes[key] = {
                label: nodes[key].label,
                behaviorType: nodes[key].behaviorType,
            };
        }
    }
    const simLinks = links.map<SimLink>((link): SimLink => {
        return {
            label: link.label,
            color: 'var(--bs-' + link.behaviorType + ')',
            type: 'one-way',
            source: simNodes[link.from],
            target: simNodes[link.to],
        };
    });
    return <NodeChart links={simLinks} nodes={Object.values(simNodes)}
                      colors={['var(--bs-gray)', ...['success', 'danger', 'warning', 'info', 'primary', 'secondary'].map((value) => 'var(--bs-' + value + ')')]}/>;
}
