import { lang } from '@i18n/i18n';
import { changeData } from '@inputConnector/actions';
import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { PaymentScheduleItem } from '../interfaces';
import { Store } from '../reducer';

interface OwnProps {
    item?: PaymentScheduleItem;
}

interface StateProps {
    value: number;
}

interface DispatchProps {
    onChange(date: string, value: number): void;
}

class Row extends React.Component<OwnProps & StateProps & DispatchProps, {}> {

    public render() {
        const {item, value, onChange} = this.props;
        return <div className={'mb-3'}>
                <span className={'form-check ' + (value ? 'text-success border-success' : '')}
                      onClick={() => {
                          onChange('' + item.id, +!value);
                      }}
                      style={{cursor: 'pointer'}}
                >
                <span className={'mr-3 ' + (value ? 'fa fa-check-square-o' : 'fa fa-square-o')}/>
                    {item.hasPayment && (
                        <i className={'mr-2 fa fa-exclamation-circle text-danger'}
                           title={lang.getText('Item has already another payment')}/>)}
                    <span>{item.label}</span>
              </span>
        </div>;
    }
}

// const {accommodation: {price}} = item;
// <span className={'text-muted'}>
// <small className={'ml-3'}>{lang.getText('Price')}: <PriceDisplay price={price}/></small>
// </span>

const mapDispatchToProps = (dispatch: Dispatch): DispatchProps => {
    return {
        onChange: (date, value) => dispatch(changeData(date, value)),
    };
};

const mapStateToProps = (state: Store, ownProps: OwnProps): StateProps => {
    const {item} = ownProps;
    let value = null;
    if (state.inputConnector.data.hasOwnProperty(item.id)) {
        value = state.inputConnector.data[item.id];
    }
    return {
        value,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Row);
