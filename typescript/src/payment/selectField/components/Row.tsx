import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { lang } from '../../../i18n/i18n';
import { changeData } from '../../../input-connector/actions';
import { PaymentAccommodationItem } from '../interfaces';
import { Store } from '../reducer';

interface Props {
    item?: PaymentAccommodationItem;
}

interface State {
    onChange?: (date: string, value: number) => void;
    value?: number;
}

class Row extends React.Component<Props & State, {}> {

    public render() {
        const {item, value, onChange} = this.props;
        return <div className={'mb-3'}>
                <span className={'form-check ' + (value ? 'text-success border-success' : '')}>
                <span
                    className={'mr-3 ' + (value ? 'fa fa-check-square-o' : 'fa fa-square-o')}
                    onClick={() => {
                        onChange('' + item.id, +!value);
                    }}
                />
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

const mapDispatchToProps = (dispatch: Dispatch): State => {
    return {
        onChange: (date, value) => dispatch(changeData(date, value)),
    };
};

const mapStateToProps = (state: Store, ownProps: Props): State => {
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
