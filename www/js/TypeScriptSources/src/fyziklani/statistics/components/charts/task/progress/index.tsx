import * as React from 'react';
import { lang } from '../../../../../../i18n/i18n';
import Chart from './chart';

interface Props {
    availablePoints: number[];
}

export default class TaskStats extends React.Component<Props, {}> {
    public render() {
        const {availablePoints} = this.props;
        return (<div className={'fyziklani-chart-container'}>
                <h3>{lang.getText('Total solved problem')}</h3>
                <Chart availablePoints={availablePoints}/>
            </div>
        );
    }
}
