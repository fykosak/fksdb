import * as React from 'react';
import { ISchedulePart } from './index';
import ScheduleItem from './schedule-item';
import TimeDisplay from '../../../shared/components/displays/time';

interface IProps {
    blockData: ISchedulePart;
    blockName: string;
}

export default class Row extends React.Component<IProps, {}> {

    public render() {
        const {blockData, blockName} = this.props;
        let component = null;
        switch (blockData.type) {
            case 'chooser':
                component = blockData.parallels.map((parallel, index) => {
                    return <div className={'col-4'}>
                        <ScheduleItem key={index} blockName={blockName} item={parallel}/>
                    </div>;
                });
                break;
            case 'info':
                component = 'INFO';
                break;

        }
        return (
            <div className={'row'}>
                <div className={'col-2'}>
                    {this.createdDateLabel(blockData.date)}
                </div>
                {component}
            </div>
        );
    }

    private createdDateLabel(dates: { start: string; end: string }) {
        return <span>
            <span className={'date-start'}>
                <TimeDisplay date={dates.start}/>
            </span>
            <span className={'timeline'}/>
            <span className={'date-end'}>
                <TimeDisplay date={dates.end}/>
            </span>
        </span>;
    }
}
