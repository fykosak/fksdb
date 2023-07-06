import * as React from 'react';
import { useContext } from 'react';
import { TranslatorContext } from '@translator/context';
import Item from 'FKSDB/Components/Charts/Core/Legend/item';

export default function Legend() {
    const translator = useContext(TranslatorContext);
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
        return <Item
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
