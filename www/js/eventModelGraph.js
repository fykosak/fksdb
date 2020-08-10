$(() => {
    const initRenderer = (r, node) => {
        const ellipse = r.ellipse(0, 0, 8, 8).attr({
            fill: '#000',
            stroke: '#000',
            "stroke-width": 0
        });
        /* set DOM node ID */
        ellipse.node.id = node.label || node.id;
        return r.set().push(ellipse);
    };

    const terminatedRenderer = (r, node) => {
        const inner = r.ellipse(0, 0, 5, 5).attr({
            fill: '#000',
            stroke: '#000',
            "stroke-width": 0
        });

        const outer = r.ellipse(0, 0, 10, 10).attr({
            fill: null,
            stroke: '#000',
            "stroke-width": 2
        });
        /* set DOM node ID */
        inner.node.id = node.label || node.id;
        return r.set().push(inner).push(outer);
    };

    const componentId = 'graph-graphComponent';
    const component = document.getElementById(componentId);
    if (component) {
        const nodes = JSON.parse(component.getAttribute('data-nodes'));
        const edges = JSON.parse(component.getAttribute('data-edges'));

        const graph = new Graph();
        nodes.forEach((node) => {
            let render = null;
            switch (node.renderer) {
                case 'init':
                    render = initRenderer;
                    break;
                case 'terminated':
                    render = terminatedRenderer;
            }
            graph.addNode(node.id, {label: node.label, render});
        });
        edges.forEach((edge) => {
            let style = null;
            let labelStyle = {};
            let label = edge.label;
            if (edge.condition !== 1) {
                labelStyle.title = edge.condition;
                label = label + '*';
            }

            if (edge.target === 'cancelled') {
                style = '#ccc';
                labelStyle.stroke = '#ccc';
            }
            graph.addEdge(edge.source, edge.target, {directed: true, label, "label-style": labelStyle, stroke: style});

        });

        var layouter = new Graph.Layout.Spring(graph);
        layouter.layout();

        /* draw the graph using the RaphaelJS draw implementation */
        var renderer = new Graph.Renderer.Raphael(component, graph, $(component).width(), 600);
        renderer.draw();
    }
});
