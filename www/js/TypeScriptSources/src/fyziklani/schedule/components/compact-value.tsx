import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { lang } from '../../../i18n/i18n';
import PriceDisplay from '../../../shared/components/displays/price';
import { IPrice } from '../../../shared/components/displays/price/interfaces';
import { toggleChooser } from '../actions';
import { IFyziklaniScheduleStore } from '../reducers/';
import {
    IData,
    IScheduleChooserItem,
} from './index';

interface IState {
    values?: {
        [key: string]: number;
    };

    onToggleChooser?(): void;
}

interface IProps {
    data: IData;
}

class CompactValue extends React.Component<IState & IProps, {}> {

    public render() {
        const {data, values, onToggleChooser} = this.props;
        const price: IPrice = {kc: 0, eur: 0};
        let count = 0;
        for (const blockName in data) {
            if (data.hasOwnProperty(blockName)) {
                const blockData = data[blockName];
                const {type} = blockData;
                if (type !== 'chooser') {
                    continue;
                }
                (blockData as IScheduleChooserItem).parallels.forEach((parallel) => {
                    for (const key in values) {
                        if (values.hasOwnProperty(key)) {
                            if ((blockName === key) && (parallel.id === values[key])) {
                                count++;
                                price.eur += +parallel.price.eur;
                                price.kc += +parallel.price.kc;
                            }
                        }
                    }
                });
            }
        }

        return (
            <div className={'schedule-compact-value input-group'}>
                <span className={'form-control'}>
                    <span>{lang.getText('Number of events')}: {count}</span>
                    <span className={'ml-3'}>{lang.getText('Price')} <PriceDisplay price={price}/></span>
                </span>
                <div className={'input-group-append'}>
                    <button className={'btn btn-fyziklani'} onClick={(event) => {
                        event.preventDefault();
                        onToggleChooser();
                    }}><span className={'fa fa-pencil mr-2'}/>{lang.getText('Edit schedule')}</button>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (store: IFyziklaniScheduleStore): IState => {
    return {
        values: store.inputConnector.data,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniScheduleStore>): IState => {
    return {
        onToggleChooser: () => dispatch(toggleChooser()),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(CompactValue);
