import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { lang } from '../../../i18n/i18n';
import { changeData } from '../../../input-connector/actions';
import Card from '../../../shared/components/card';
import PriceDisplay from '../../../shared/components/displays/price';
import { IFyziklaniScheduleStore } from '../reducers/';
import { IScheduleItem } from './index';

interface IProps {
    item: IScheduleItem;
    blockName: string;
}

interface IState {
    value?: number;

    setSchedule?(id: number): void;
}

class ScheduleItem extends React.Component<IProps & IState, {}> {

    public render() {
        const {item, value} = this.props;
        const langKey = lang.getCurrentLocale();
        if (!item.hasOwnProperty(langKey)) {
            return null;
        }
        const active = item.id === value;
        const localizedData = item[langKey];
        return (

            <div onClick={() => {
                this.props.setSchedule(active ? null : item.id);
            }}>
                <Card headline={localizedData.name} level={active ? 'success' : 'info'}>
                    <span>{localizedData.description}</span>
                    <span>{localizedData.place}</span>
                    <PriceDisplay price={item.price}/>
                </Card>
            </div>
        );
    }
}

const mapStateToProps = (store: IFyziklaniScheduleStore, ownProps: IProps): IState => {
    const {blockName} = ownProps;
    return {
        value: store.inputConnector.data.hasOwnProperty(blockName) ? store.inputConnector.data[blockName] : null,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniScheduleStore>, ownProps: IProps): {} => {
    return {
        setSchedule: (id: number) => dispatch(changeData(ownProps.blockName, id)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(ScheduleItem);
