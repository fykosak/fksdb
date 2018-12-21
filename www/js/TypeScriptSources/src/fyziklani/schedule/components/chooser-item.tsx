import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { lang } from '../../../i18n/i18n';
import { changeData } from '../../../input-connector/actions';
import PriceDisplay from '../../../shared/components/displays/price';
import { IFyziklaniScheduleStore } from '../reducers/';
import { IChooserParallel } from './index';
import Item from './item';

interface IProps {
    item: IChooserParallel;
    blockName: string;
}

interface IState {
    value?: number;

    setSchedule?(id: number): void;
}

class ChooserItem extends React.Component<IProps & IState, {}> {

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
                <Item className={'chooser-container ' + (active ? 'active' : '')}
                      icon={<span className={active ? 'w-100 fa fa-check-square-o' : 'w-100 fa fa-square-o'}/>}>
                    <span className={'font-weight-bold'}>{localizedData.name}</span>
                    {localizedData.description && (<div className={'font-italic'}>
                        {localizedData.description}
                    </div>)}
                    <div className={'small'}>
                        <PriceDisplay price={item.price}/>
                    </div>
                </Item>
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

const mapDispatchToProps = (dispatch: Dispatch<Action>, ownProps: IProps): IState => {
    return {
        setSchedule: (id: number) => dispatch(changeData(ownProps.blockName, id)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(ChooserItem);
