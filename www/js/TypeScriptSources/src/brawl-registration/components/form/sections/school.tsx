import * as React from 'react';
import {
    Field,
    FormSection,
} from 'redux-form';
import Lang from '../../../../lang/components/lang';
import InputProvider from '../../../../person-provider/components/input-provider';
import { getFieldName } from '../../../middleware/person';
import { IPersonSelector } from '../../../middleware/price';
import StudyYearField from '../../inputs/study-year';
import SchoolField from '../fields/school-provider';

export default class SchoolSection extends React.Component<IPersonSelector, {}> {
    public render() {
        const {type, index} = this.props;
        return <div>
            <h3><Lang text={'School'}/></h3>
            <Field
                accessKey={getFieldName(type, index)}
                JSXLabel={<Lang text={'School'}/>}
                providerInput={SchoolField}
                type={null}
                secure={true}
                component={InputProvider}
                modifiable={true}
                name={'personHistory.schoolId'}
                required={true}
            />
            <Field
                accessKey={getFieldName(type, index)}
                JSXLabel={<Lang text={'Study year'}/>}
                type={null}
                secure={true}
                component={InputProvider}
                providerInput={StudyYearField}
                modifiable={true}
                name={'personHistory.studyYear'}
                required={true}
            />
        </div>;
    }
}
