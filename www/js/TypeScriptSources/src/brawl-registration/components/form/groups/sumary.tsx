import * as React from 'react';
import { connect } from 'react-redux';
import { FORM_NAME } from '../';
import {
    getAccommodationFromState,
    getAccommodationPrice,
} from '../../../../person-provider/components/fields/person-accommodation/accommodation/helpers';
import {
    IAccommodationItem,
    IPersonAccommodation,
} from '../../../../person-provider/components/fields/person-accommodation/accommodation/interfaces';
import {
    IScheduleItem,
} from '../../../middleware/iterfaces';
import {
    getParticipantValues,
    getScheduleFromState,
    getSchedulePrice,
    IPersonSelector,
} from '../../../middleware/price';
import { IStore } from '../../../reducers';
import NameDisplay from '../../../../shared/components/displays/name';
import PriceDisplay from '../../../../shared/components/displays/price';

interface IDataProps {
    accommodation: IPersonAccommodation;
    personSelector: IPersonSelector;
    schedule: boolean[];
    name: {
        familyName: string;
        otherName: string;
    };
}

interface IState {
    data?: IDataProps[];
    accommodationDef?: IAccommodationItem[];
    scheduleDef?: IScheduleItem[];
}

class Summary extends React.Component<IState, {}> {

    public render() {
        const rows = [];
        const {accommodationDef, scheduleDef} = this.props;
        const accSum = {kc: 0, eur: 0};
        const scheduleSum = {kc: 0, eur: 0};

        this.props.data.forEach((personData, index) => {
            const accommodationPrice = getAccommodationPrice(accommodationDef, personData.accommodation);
            const schedulePrice = getSchedulePrice(scheduleDef, personData.schedule);
            accSum.kc += accommodationPrice.kc;
            accSum.eur += accommodationPrice.eur;

            scheduleSum.kc += schedulePrice.kc;
            scheduleSum.eur += schedulePrice.eur;

            rows.push(<tr key={index}>
                <td><span className={(personData.personSelector.type === 'teacher') ? 'fa fa-graduation-cap' : 'fa fa-user'}/></td>
                <td>
                    <NameDisplay personSelector={personData.personSelector}/>
                </td>
                <td>
                    <PriceDisplay price={accommodationPrice}/>
                </td>
                <td>
                    <PriceDisplay price={schedulePrice}/>
                </td>
                <td>
                    <PriceDisplay price={{
                        eur: (schedulePrice.eur + accommodationPrice.eur),
                        kc: (schedulePrice.kc + accommodationPrice.kc),
                    }}/>
                </td>
            </tr>);
        });

        rows.push(<tr key={'sum'} className="table-primary">
            <td/>
            <td>
                sum
            </td>
            <td>
                <PriceDisplay price={accSum}/>
            </td>
            <td>
                <PriceDisplay price={scheduleSum}/>
            </td>
            <td>
                <PriceDisplay price={{
                    eur: (scheduleSum.eur + accSum.eur),
                    kc: (scheduleSum.kc + accSum.kc),
                }}/>
            </td>
        </tr>);
        return <div>
            <table className="table table-striped">
                <thead>
                <tr>
                    <th/>
                    <th>name</th>
                    <th>Accommodation price</th>
                    <th>Schedule price</th>
                    <th>total</th>
                </tr>
                </thead>
                <tbody>
                {rows}
                </tbody>
            </table>
        </div>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore): IState => {
    const data: IDataProps[] = [];
    state.definitions.persons.forEach((person) => {
        const formValues = getParticipantValues(FORM_NAME, state, person.personSelector);
        data.push({
            accommodation: getAccommodationFromState(FORM_NAME, state, person.personSelector),
            name: {
                familyName: formValues.familyName,
                otherName: formValues.otherName,
            },
            personSelector: person.personSelector,
            schedule: getScheduleFromState(FORM_NAME, state, person.personSelector),
        });
    });

    return {
        accommodationDef: state.definitions.accommodation,
        data,
        scheduleDef: state.definitions.schedule,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Summary);
