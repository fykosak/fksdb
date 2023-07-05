import * as React from 'react';
import './legend.scss';
import Item, { LegendItemDatum } from 'FKSDB/Components/Charts/Core/Legend/item';

interface OwnProps {
    data: LegendItemDatum[];
}

export default class Legend extends React.Component<OwnProps, never> {
    public render() {
        const {data} = this.props;

        return <div className="chart-legend row row-cols-lg-5">
            {data.map((item, index) => {
                return <Item item={item} key={index}/>
            })}
        </div>;
    }

}
