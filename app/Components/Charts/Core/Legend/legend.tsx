import * as React from 'react';
import './legend.scss';
import LegendItem, { LegendItemDatum } from 'FKSDB/Components/Charts/Core/Legend/legend-item';

interface OwnProps {
    data: LegendItemDatum[];
}

export default class Legend extends React.Component<OwnProps, never> {
    public render() {
        const {data} = this.props;

        return <div className="chart-legend row row-cols-lg-5">
            {data.map((item, index) => {
                return <LegendItem item={item} key={index}/>
            })}
        </div>;
    }

}
