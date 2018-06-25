import * as React from 'react';
import { connect } from 'react-redux';
import { Field } from 'redux-form';
import { IPersonSelector } from '../../../../../brawl-registration/middleware/price';
import { IStore } from '../../../../../brawl-registration/reducers';
import Lang from '../../../../../lang/components/lang';
import { IInputDefinition } from '../../interfaces';
import { IAccommodationItem } from './interfaces';
import Price from './price';
import Row from './row';

interface IProps {
    personSelector: IPersonSelector;
    inputDef: IInputDefinition;
}

interface IState {
    accommodationDef?: IAccommodationItem[];
}

class Accommodation extends React.Component<IProps & IState, {}> {

    public render() {
        const dates = {};
        const names = [];
        const {accommodationDef, personSelector} = this.props;
        accommodationDef.forEach((value) => {
            if (names.indexOf(value.name) === -1) {
                names.push(value.name);
            }
            dates[value.date] = dates[value.date] || [];
            dates[value.date].push(value);
        });

        const rows = [];
        for (const date in dates) {
            if (dates.hasOwnProperty(date)) {
                rows.push(<Field
                    key={date}
                    name={date}
                    component={Row}
                    hotels={names}
                    date={date}
                    accommodations={dates[date]}
                />);
            }
        }

        return <>
            <table className="table">
                <thead>
                <tr>
                    <th><Lang text={'date'}/></th>
                    {names.map((hotel, i) => {
                        return <th key={i}>{hotel}</th>;
                    })}
                </tr>
                </thead>
                <tbody>
                {rows}
                </tbody>
            </table>
            <Price personSelector={personSelector}/>
        </>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore): IState => {
    return {
        accommodationDef: state.definitions.accommodation,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Accommodation);
