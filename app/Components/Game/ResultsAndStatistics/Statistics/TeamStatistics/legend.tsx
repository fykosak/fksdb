import * as React from 'react';
import { useContext } from 'react';
import { TranslatorContext } from '@translator/context';
import { Item } from 'FKSDB/Components/Charts/Core/Legend/legend';

export default function Legend() {
    const translator = useContext(TranslatorContext);
    const availablePoints = [1, 2, 3, 5];
    const legend = availablePoints.map((points: number) => {
        const pointsLabel = translator.nGetText('point', 'points', points);
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
