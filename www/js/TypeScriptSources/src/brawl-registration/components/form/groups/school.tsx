import * as React from 'react';
import Lang from '../../../../lang/components/lang';
import SchoolField from '../../form/sections/school-provider';
import Input from '../../inputs/input';
import StudyYearField from '../../inputs/study-year';

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

        return <div>
            <h3><Lang text={'School'}/></h3>
            <Input label={'School'}
                   type={null}
                   secure={true}
                   component={SchoolField}
                   modifiable={true}
                   name={'school'}
                   providerOptions={school}
                   required={true}
            />
            <Input label={'Study year'}
                   type={null}
                   secure={true}
                   component={StudyYearField}
                   modifiable={true}
                   name={'studyYear'}
                   providerOptions={studyYear}
                   required={true}
            />
        </div>;
    }
}
