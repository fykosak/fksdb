import * as React from 'react';
import ChooserItem from './chooser-item';
import { IScheduleItem } from './index';
import InfoItem from './info-item';
import TimeLabel from './time-label';

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
                    return <div key={index} className={'col-6'}>
                        <ChooserItem blockName={blockName} item={parallel}/>
                    </div>;
                });
                break;
            case 'info':
                component = <div className={'col-6'}>
                    <InfoItem blockName={blockName} item={blockData.descriptions}/>
                </div>;
                break;
            default:
                throw new Error('Unsupported type:' + type);

        }
        return (
            <div className={'schedule-row row schedule-row-' + blockData.type}>
                <div className={'time-block col-2'}>
                    <TimeLabel start={blockData.date.start} end={blockData.date.end}/>
                </div>
                <div className={'schedule-container col-10 row justify-content-between'}>
                    {component}
                </div>
            </div>
        );
    }
}
