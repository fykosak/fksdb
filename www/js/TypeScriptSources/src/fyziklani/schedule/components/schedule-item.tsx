import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { lang } from '../../../i18n/i18n';
import { changeData } from '../../../input-connector/actions';
import PriceDisplay from '../../../shared/components/displays/price';
import { IFyziklaniScheduleStore } from '../reducers/';
import { IChooserParallel } from './index';

interface IProps {
    item: IChooserParallel;
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

            <div className={'schedule-container'} onClick={() => {
                this.props.setSchedule(active ? null : item.id);
            }}>
                <div className={'schedule-inner-container row ' + (active ? 'active' : '')}>
                    <div className={'col-2 schedule-check-container'}>
                        <span className={'h1 mr-2 ' + (active ? 'fa fa-check-square-o' : 'fa fa-square-o')}/>
                    </div>
                    <div className={'col-10'}>
                        <h6>{localizedData.name}</h6>
                        <div>{localizedData.description}</div>
                        <div>
                            <small>{localizedData.place}</small>
                        </div>
                        <div>
                            <small>
                                <PriceDisplay price={item.price}/>
                            </small>
                        </div>
                    </div>
                </div>
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
