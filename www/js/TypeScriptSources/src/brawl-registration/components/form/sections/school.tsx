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

interface IState {
    school?: IProviderValue<ISchool>;
    studyYear?: IProviderValue<number>;
}

class SchoolSection extends React.Component<IState & IPersonSelector, {}> {
    public render() {
        const {school, studyYear} = this.props;

        return <FormSection name={'school'}>
            <h3><Lang text={'School'}/></h3>
            <Input label={<Lang text={'School'}/>}
                   type={null}
                   secure={true}
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
                   modifiable={true}
                   name={'studyYear'}
                   providerOptions={studyYear}
                   required={true}
            />
        </FormSection>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
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
