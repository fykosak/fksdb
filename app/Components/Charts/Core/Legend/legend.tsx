import * as React from 'react';
import './legend.scss';
import Item, { LegendItemDatum } from 'FKSDB/Components/Charts/Core/Legend/item';

interface OwnProps {
    data: LegendItemDatum[];
}

export default function Legend(props: OwnProps) {
    const {data} = props;
    return <div className="chart-legend row row-cols-lg-5">
        {data.map((item, index) => {
            return <Item item={item} key={index}/>
        })}
    </div>;
}
