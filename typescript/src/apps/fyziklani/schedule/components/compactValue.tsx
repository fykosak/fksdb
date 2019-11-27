import { lang } from '@i18n/i18n';
import PriceDisplay from '@shared/components/displays/price/';
import { Price } from '@shared/components/displays/price/interfaces';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { toggleChooser } from '../actions';
import { Store as ScheduleStore } from '../reducers/';
import {
    Data,
    ScheduleChooserItem,
} from './index';

interface StateProps {
    values: {
        [key: string]: number;
    };
}

interface DispatchProps {
    onToggleChooser(): void;
}

interface OwnProps {
    data: Data;
}

class CompactValue extends React.Component<StateProps & DispatchProps & OwnProps, {}> {

    public render() {
        const {data, values, onToggleChooser} = this.props;
        const price: Price = {kc: 0, eur: 0};
        let count = 0;
        for (const blockName in data) {
            if (data.hasOwnProperty(blockName)) {
                const blockData = data[blockName];
                const {type} = blockData;
                if (type !== 'chooser') {
                    continue;
                }
                (blockData as ScheduleChooserItem).parallels.forEach((parallel) => {
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

const mapStateToProps = (store: ScheduleStore): StateProps => {
    return {
        values: store.inputConnector.data,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): DispatchProps => {
    return {
        onToggleChooser: () => dispatch(toggleChooser()),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(CompactValue);
