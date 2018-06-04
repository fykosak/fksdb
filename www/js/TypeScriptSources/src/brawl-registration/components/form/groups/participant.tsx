import * as React from 'react';
import { connect } from 'react-redux';
import { FORM_NAME } from '../';
import {
    getAccommodationFromState,
    getScheduleFromState,
    IPersonAccommodation,
} from '../../../middleware/price';
import AccommodationGroup from '../sections/accommodation';
import BaseInfoGroup from '../sections/base-info';
import ScheduleGroup from '../sections/schedule';
import SchoolGroup from '../sections/school';

import { IProviderValue } from '../../../../person-provider/interfaces';
import { getFieldName } from '../../../middleware/person';
import { IStore } from '../../../reducers';
import Price from '../fields/price';
import IdNumberSection from '../sections/id-number';
import { IScheduleItem } from '../../../middleware/iterfaces';

interface IState {
    accommodation?: IPersonAccommodation;
    providerOpt?: {
        accommodation?: IProviderValue<string>;
        email?: IProviderValue<string>;
        familyName?: IProviderValue<string>;
        idNumber?: IProviderValue<string>;
        otherName?: IProviderValue<string>;
        personId?: IProviderValue<string>;
        school?: IProviderValue<any>;
        studyYear?: IProviderValue<string>;
    };
    schedule?: boolean[];
    scheduleDef?: IScheduleItem[];
}

interface IProps {
    type: string;
    index: number;
}

class ParticipantForm extends React.Component<IState & IProps, {}> {
    public render() {
        const {providerOpt: {otherName, familyName, email, idNumber, school, studyYear, personId}, accommodation, type, index, scheduleDef, schedule} = this.props;
        return <>
            <BaseInfoGroup providerOpt={{familyName, otherName, email}} type={type} index={index}/>
            <SchoolGroup type={type} index={index} providerOpt={{school, studyYear}}/>
            <AccommodationGroup type={type} index={index}/>
            <ScheduleGroup type={type} index={index} providerOpt={{}}/>
            <IdNumberSection type={type}
                             index={index}
                             accommodation={accommodation}
                             providerOpt={{idNumber}}
                             schedule={schedule}
                             scheduleDef={scheduleDef}/>
            <Price type={type} index={index}/>
        </>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore, ownProps: IProps): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        // const fieldNames = ['personId', 'email', 'school', 'studyYear', 'accommodation', 'idNumber', 'familyName', 'otherName'];
        return {
            accommodation: getAccommodationFromState(FORM_NAME, state, ownProps),
            providerOpt: {
                accommodation: state.provider[accessKey].fields.accommodation,
                email: state.provider[accessKey].fields.email,
                familyName: state.provider[accessKey].fields.familyName,
                idNumber: state.provider[accessKey].fields.idNumber,
                otherName: state.provider[accessKey].fields.otherName,
                personId: state.provider[accessKey].fields.personId,
                school: state.provider[accessKey].fields.school,
                studyYear: state.provider[accessKey].fields.studyYear,
            },
            schedule: getScheduleFromState(FORM_NAME, state, ownProps),
            scheduleDef: state.definitions.schedule,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(ParticipantForm);
