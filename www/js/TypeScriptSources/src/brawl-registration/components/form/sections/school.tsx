import * as React from 'react';
import { connect } from 'react-redux';
import { FormSection } from 'redux-form';
import Lang from '../../../../lang/components/lang';
import { getFieldName } from '../../../middleware/person';
import { IPersonSelector } from '../../../middleware/price';
import { IStore } from '../../../reducers';
import Input from '../../inputs/input';
import StudyYearField from '../../inputs/study-year';
import SchoolField from '../fields/school-provider';
import { IProviderValue } from '../../../../person-provider/interfaces';
import { ISchool } from '../fields/school-provider/interfaces';
import { Dispatch } from 'redux';
import { clearProviderProviderProperty } from '../../../../person-provider/actions';

interface IState {
    school?: IProviderValue<ISchool>;
    studyYear?: IProviderValue<number>;
    removeSchoolValue?: () => void;
    removeStudyYearValue?: () => void;
}

class SchoolSection extends React.Component<IState & IPersonSelector, {}> {
    public render() {
        const {school, studyYear, removeSchoolValue, removeStudyYearValue} = this.props;

        return <FormSection name={'school'}>
            <h3><Lang text={'School'}/></h3>
            <Input label={<Lang text={'School'}/>}
                   type={null}
                   secure={true}
                   removeProviderValue={removeSchoolValue}
                   component={SchoolField}
                   modifiable={true}
                   name={'school'}
                   providerOptions={school}
                   required={true}
            />
            <Input label={<Lang text={'Study year'}/>}
                   type={null}
                   secure={true}
                   component={StudyYearField}
                   removeProviderValue={removeStudyYearValue}
                   modifiable={true}
                   name={'studyYear'}
                   providerOptions={studyYear}
                   required={true}
            />
        </FormSection>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>, ownProps: IPersonSelector): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    return {
        removeSchoolValue: () => dispatch(clearProviderProviderProperty(accessKey, 'school')),
        removeStudyYearValue: () => dispatch(clearProviderProviderProperty(accessKey, 'studyYear')),
    };
};

const mapStateToProps = (state: IStore, ownProps: IPersonSelector): IState => {
    const accessKey = getFieldName(ownProps.type, ownProps.index);
    if (state.provider.hasOwnProperty(accessKey)) {
        return {
            school: state.provider[accessKey].fields.school,
            studyYear: state.provider[accessKey].fields.studyYear,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(SchoolSection);
