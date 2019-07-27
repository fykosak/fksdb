import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { changeData } from '../../../../input-connector/actions';
import DateDisplay from '../../../../shared/components/displays/Date';
import PriceDisplay from '../../../../shared/components/displays/price/Index';
import { EventAccommodation } from '../../middleware/interfaces';
import { Store  } from '../../reducer/';

interface Props {
    accommodations: EventAccommodation[];
    hotels: string[];
    date: string;
}

interface State {
    onChange?: (value: number) => void;
    value?: number;
}

class Row extends React.Component<Props & State, {}> {
    public render() {
        const {hotels, accommodations, date, onChange, value} = this.props;
        if (!accommodations) {
            return null;
        }
        const cols = [];
        hotels.forEach((name, index) => {
            const currentAcc = accommodations.filter((acc) => {
                return acc.name === name;
            });
            if (currentAcc.length) {
                const priceLabel = <small className="align-bottom text-muted">
                    <PriceDisplay price={currentAcc[0].price}/>
                </small>;
                const capacityLabel = <small
                    className={(currentAcc[0].capacity === currentAcc[0].usedCapacity) ? 'text-danger' : ''}>
                    {currentAcc[0].capacity}/{currentAcc[0].usedCapacity}
                </small>;
                if (currentAcc[0].eventAccommodationId === value) {
                    cols.push(<td key={index} className="text-center table-success"
                                  onClick={() => {
                                      onChange(null);
                                  }}>
                        <div>
                            <span className="text-success fa fa-check"/>
                        </div>
                        <div>{priceLabel}</div>
                        <div>{capacityLabel}</div>
                    </td>);
                } else {

                    cols.push(<td key={index} className="text-center table-secondary" onClick={() => {
                        onChange(currentAcc[0].eventAccommodationId);
                    }}>
                        <div>{priceLabel}</div>
                        <div>{capacityLabel}</div>
                    </td>);

                }
            } else {
                cols.push(<td key={index} className="table-danger"/>);
            }
        });
        return <tr>
            <td><label><DateDisplay date={date}/></label></td>
            {cols}
        </tr>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch, ownProps: Props): State => {
    return {
        onChange: (value) => dispatch(changeData(ownProps.date, value)),
    };
};

const mapStateToProps = (state: Store, ownProps: Props): State => {
    let value = null;
    if (state.inputConnector.data.hasOwnProperty(ownProps.date)) {
        value = state.inputConnector.data[ownProps.date];
    }
    return {
        value,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Row);
