import * as React from 'react';
import DateDisplay from '../../../shared/components/displays/date';
import TimeDisplay from '../../../shared/components/displays/time';
import ChooserItem from './chooser-item';
import { IScheduleItem } from './index';
import InfoItem from './info-item';

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
                    return <div className={'col-6'}><ChooserItem key={index} blockName={blockName} item={parallel}/></div>;
                });
                break;
            case 'info':
                component = <div className={'col-6'}><InfoItem blockName={blockName} item={blockData.descriptions}/></div>;
                break;
            default:
                throw new Error('Unsupported type:' + type);

        }
        return (
            <div className={'schedule-row schedule-row-' + blockData.type}>
                <div className={'time-block'}>
                    {this.createdDateLabel(blockData.date)}
                </div>
                <div className={'schedule-container row justify-content-between'}>
                    {component}
                </div>
            </div>
        );
    }

    private createdDateLabel(dates: { start: string; end: string }) {
        return <div className={'schedule-time'}>
            <div>
                <DateDisplay date={dates.start} options={{weekday: 'long'}}/>
            </div>
            <div>
                <TimeDisplay
                    date={dates.start}
                    options={{hour: 'numeric', minute: 'numeric'}}
                /> - <TimeDisplay
                date={dates.end}
                options={{hour: 'numeric', minute: 'numeric'}}
            />
            </div>
        </div>;
    }
}
