import * as React from 'react';
import { connect } from 'react-redux';
import { getAccommodationFromState } from '../../middleware/price';
import { FORM_NAME } from '../form/';
import AccommodationGroup from '../form/groups/accommodation';
import BaseInfoGroup from '../form/groups/base-info';
import ScheduleGroup from '../form/groups/schedule';
import SchoolGroup from '../form/groups/school';
import { getFieldName } from './persons';
import Price from './price';

interface IState {
    accommodation?: any;
    providerOpt?: {
        accommodation?: { hasValue: boolean; value: string };
        email?: { hasValue: boolean; value: string };
        familyName?: { hasValue: boolean; value: string };
        idNumber?: { hasValue: boolean; value: string };
        otherName?: { hasValue: boolean; value: string };
        personId?: { hasValue: boolean; value: string };
        school?: { hasValue: boolean; value: string };
        studyYear?: { hasValue: boolean; value: string };
    };
}

interface IProps {
    type: string;
    index: number;
}

class ParticipantForm extends React.Component<IState & IProps, {}> {
    public render() {
        const {providerOpt: {otherName, familyName, email, idNumber, school, studyYear, personId}, accommodation, type, index} = this.props;
        return <>
            <BaseInfoGroup providerOpt={{familyName, otherName, email}} type={this.props.type} index={this.props.index}/>
            <SchoolGroup type={this.props.type} index={this.props.index} providerOpt={{school, studyYear}}/>
            <AccommodationGroup accommodation={accommodation} providerOpt={{idNumber}} type={this.props.type} index={this.props.index}/>
            <ScheduleGroup type={this.props.type} index={this.props.index} providerOpt={{}}/>
            <Price type={type} index={index}/>
        </>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state, ownProps: IProps): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        // const fieldNames = ['personId', 'email', 'school', 'studyYear', 'accommodation', 'idNumber', 'familyName', 'otherName'];
        return {
            ...getAccommodationFromState(FORM_NAME, state, ownProps),
            providerOpt: {
                accommodation: state.provider[accessKey].accommodation,
                email: state.provider[accessKey].email,
                familyName: state.provider[accessKey].familyName,
                idNumber: state.provider[accessKey].idNumber,
                otherName: state.provider[accessKey].otherName,
                personId: state.provider[accessKey].personId,
                school: state.provider[accessKey].school,
                studyYear: state.provider[accessKey].studyYear,
            },
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(ParticipantForm);
