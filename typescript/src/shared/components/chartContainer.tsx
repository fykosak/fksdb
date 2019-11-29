import * as React from 'react';
import Legend from '../../apps/fyziklani/statistics/components/charts/team/legend';

interface OwnProps<T extends {}> {
    chart: React.ComponentClass<T> | any; // TODO fix ConnectedComponent
    chartProps: T;
    includeLegend: boolean;
    headline: string;
}

export default class ChartContainer<T> extends React.Component<OwnProps<T>, {}> {

    public render() {
        const {includeLegend, headline, chartProps, chart} = this.props;
        return (
            <div className={'chart-container'}>
                <h3>{headline}</h3>
                <div className={'row'}>
                    <div className="col-12">
                        {React.createElement<T>(chart, {...chartProps})}
                    </div>
                    {includeLegend && <div className="col-12">
                        <Legend inline={false}/>
                    </div>}
                </div>
            </div>
        );
    }
}
