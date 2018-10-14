import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import Powered from '../../../shared/powered';
import { IFyziklaniScheduleStore } from '../reducers/';
import {
    IData,
    IScheduleItem,
} from './index';
import PriceDisplay from '../../../shared/components/displays/price';

interface IState {
}

interface IProps {
    data: IData;
}

class RoutingApp extends React.Component<IState & IProps, {}> {

    public render() {
        const {data} = this.props;
        const rows = [];
        for (const blockName in data) {
            if (data.hasOwnProperty(blockName)) {
                const blockData = data[blockName];

                rows.push(<div>{this.createdDateLabel(blockData.date)}</div>);
                blockData.parallels.map((item, index) => {
                    return this.createItem(item, index);
                });
            }
        }

        return (
            <div>
                <Powered/>
            </div>
        );
    }

    private createdDateLabel(dates: { start: string; end: string }) {
        return <span>{dates.start}{dates.end}</span>;
    }

    private createItem(item: IScheduleItem, index: number) {
        return <div key={index}
                    onClick={() => {
                        console.log(item.id);
                    }}>
            <div>{item.name}</div>
            <div>{item.description}</div>
            <div><PriceDisplay price={item.price}/></div>
        </div>;
    }
}

const mapStateToProps = (): IState => {
    return {};
};

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniScheduleStore>): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(RoutingApp);
