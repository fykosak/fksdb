import * as React from 'react';
import DateDisplay from '../../../shared/components/displays/date';
import TimeDisplay from '../../../shared/components/displays/time';
import { IScheduleItem } from './index';
import InfoItem from './Info-item';
import ScheduleItem from './schedule-item';

interface IProps {
    blockData: IScheduleItem;
    blockName: string;
}

export default class Row extends React.Component<IProps, {}> {

    public render() {
        const {blockData, blockName} = this.props;
        let component = null;
        const {type} = blockData;
        switch (blockData.type) {
            case 'chooser':
                component = blockData.parallels.map((parallel, index) => {
                    return <div className={'col-5'}><ScheduleItem key={index} blockName={blockName} item={parallel}/></div>;
                });
                break;
            case 'info':
                component = <div className={'col-5'}><InfoItem blockName={blockName} item={blockData.descriptions}/></div>;
                break;
            default:
                throw new Error('Unsupported type:' + type);

        }
        // <div className={'schedule-line'}/>
        return (
            <div className={'schedule-row schedule-row-' + blockData.type}>
                <div className={'time-block'}>
                    {this.createdDateLabel(blockData.date)}
                </div>
                <div className={'schedule-block row justify-content-between'}>
                    {component}
                </div>

            </div>
        );
    }

    private createdDateLabel(dates: { start: string; end: string }) {
        return <div className={'schedule-time'}>
            <div className={'date-start'}>
                <DateDisplay date={dates.start} options={{weekday: 'short'}}/> <TimeDisplay date={dates.start}/>
            </div>
            <div className={'timeline'}>|</div>
            <div className={'date-end'}>
                <DateDisplay date={dates.start} options={{weekday: 'short'}}/> <TimeDisplay date={dates.end}/>
            </div>
        </div>;
    }
}
