import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import DateDisplay from '../../shared/components/displays/date';
import PriceDisplay from '../../shared/components/displays/price';
import { changeAccommodation } from '../actions';
import { IEventAccommodation } from '../middleware/interfaces';
import { IAccommodationStore } from '../reducer';

interface IProps {
    accommodations: IEventAccommodation[];
    hotels: string[];
    date: string;
}

interface IState {
    onChange?: (value: number) => void;
    value?: number;
}

class Row extends React.Component<IProps & IState, {}> {
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

const mapDispatchToProps = (dispatch: Dispatch<IAccommodationStore>, ownProps: IProps): IState => {
    return {
        onChange: (value) => dispatch(changeAccommodation(ownProps.date, value)),
    };
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    let value = null;
    if (state.accommodation.hasOwnProperty(ownProps.date)) {
        value = state.accommodation[ownProps.date];
    }
    return {
        value,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Row);
