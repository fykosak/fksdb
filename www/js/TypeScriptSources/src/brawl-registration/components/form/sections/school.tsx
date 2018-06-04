import * as React from 'react';
import Lang from '../../../../lang/components/lang';
import SchoolField from '../fields/school-provider';
import Input from '../../inputs/input';
import StudyYearField from '../../inputs/study-year';
import { FormSection } from 'redux-form';

interface IProps {
    type: string;
    index: number;
    providerOpt: {
        school: { hasValue: boolean; value: string };
        studyYear: { hasValue: boolean; value: string };
    };
}

export default class SchoolGroup extends React.Component<IProps, {}> {
    public render() {
        const {providerOpt: {school, studyYear}} = this.props;

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
