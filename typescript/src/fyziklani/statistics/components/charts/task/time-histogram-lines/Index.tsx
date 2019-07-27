import * as React from 'react';
import { lang } from '@i18n/i18n';
import Legend from '../../team/Legend';
import Chart from './Chart';

interface Props {
    taskId: number;
    availablePoints: number[];
}

export default class Timeline extends React.Component<Props, {}> {

    public render() {
        const {taskId, availablePoints} = this.props;
        return (
            <div className={'fyziklani-chart-container'}>
                <h3>{lang.getText('Time histogram')}</h3>
                <div className={'row'}>
                    <div className="col-8">
                        <Chart taskId={taskId} availablePoints={availablePoints}/>
                    </div>
                    <div className="col-4">
                        <Legend inline={false}/>
                    </div>
                </div>
            </div>
        );
    }
}
