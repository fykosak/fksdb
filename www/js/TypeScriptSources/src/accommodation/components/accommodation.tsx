import * as React from 'react';
import { connect } from 'react-redux';
// import Lang from '../../../../../lang/components/lang';
import { IAccommodationItem } from '../middleware/interfaces';
import Price from './price';
import Row from './row';

interface IProps {
    accommodationDef?: IAccommodationItem[];
}

interface IState {

}

class Accommodation extends React.Component<IProps & IState, {}> {

    public render() {
        const dates = {};
        const names = [];
        const {accommodationDef} = this.props;
        if (!accommodationDef) {
            return null;
        }
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
                rows.push(<Row
                    key={date}
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
                    <th>Date</th>
                    {names.map((hotel, i) => {
                        return <th key={i}>{hotel}</th>;
                    })}
                </tr>
                </thead>
                <tbody>
                {rows}
                </tbody>
            </table>
            <Price/>
        </>;
    }
} // <Lang text={'date'}/>

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state): IState => {
    return {
        //   accommodationDef: state.definitions.accommodation,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Accommodation);
