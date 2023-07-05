import * as React from 'react';
import { TranslatorContext } from '@translator/LangContext';
import LegendItem from 'FKSDB/Components/Charts/Core/Legend/legend-item';

export default class Legend extends React.Component<Record<never, never>, never> {
    static contextType = TranslatorContext;

    public render() {
        const translator = this.context;
        const availablePoints = [1, 2, 3, 5];
        const legend = availablePoints.map((points: number) => {
            let pointsLabel;
            switch (points) {
                case 1:
                    pointsLabel = translator.getText('bod');
                    break;
                case 2:
                case 3:
                    pointsLabel = translator.getText('body');
                    break;
                default:
                    pointsLabel = translator.getText('bodů');
            }
            return <LegendItem
                key={points}
                item={{
                    name: points + ' ' + pointsLabel,
                    color: 'var(--color-fof-points-' + points + ')',
                    display: {
                        points: true,
                    },
                }}/>;
        });

        return <div className="chart-legend row row-cols-lg-5">
            {legend}
        </div>;
    }
}
