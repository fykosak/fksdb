import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { lang } from '../../../i18n/i18n';
import { changeData } from '../../../input-connector/actions';
import PriceDisplay from '../../../shared/components/displays/price';
import { Store as ScheduleStore } from '../reducers/';
import { ChooserParallel } from './index';
import Item from './item';

interface Props {
    item: ChooserParallel;
    blockName: string;
}

interface State {
    value?: number;

    setSchedule?(id: number): void;
}

class ChooserItem extends React.Component<Props & State, {}> {

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

const mapStateToProps = (store: ScheduleStore, ownProps: Props): State => {
    const {blockName} = ownProps;
    return {
        value: store.inputConnector.data.hasOwnProperty(blockName) ? store.inputConnector.data[blockName] : null,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>, ownProps: Props): State => {
    return {
        setSchedule: (id: number) => dispatch(changeData(ownProps.blockName, id)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(ChooserItem);
