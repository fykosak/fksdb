import * as React from 'react';
import { lang } from '../../../../../../i18n/i18n';
import Chart from './chart';

interface IProps {
    taskId: number;
    availablePoints: number[];
}

export default class Timeline extends React.Component<IProps, {}> {
    public constructor(props, context) {
        super(props, context);
        this.state = {from: props.gameStart, to: props.gameEnd};
    }

    public render() {
        const {taskId} = this.props;
        return (
            <div className={'fyziklani-chart-container'}>
                <h3>{lang.getText('timeProgress')}</h3>
                <Chart taskId={taskId}/>
            </div>
        );
    }
}
