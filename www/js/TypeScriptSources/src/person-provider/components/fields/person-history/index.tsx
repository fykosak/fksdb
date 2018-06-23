import * as React from 'react';
import { FormSection } from 'redux-form';
import { ISectionProps } from '../interfaces';
import SchoolId from './school-id';
import StudyYear from './study-year';

export default class PersonHistory extends React.Component<ISectionProps, {}> {
    public render() {
        const fields = [];
        const {sectionDef, personSelector} = this.props;
        for (const inputName in sectionDef.fields) {
            if (sectionDef.fields.hasOwnProperty(inputName)) {
                const inputDef = sectionDef.fields[inputName];
                switch (inputName) {
                    case 'school_id':
                        fields.push(<SchoolId name={inputName} personSelector={personSelector} def={inputDef}/>);
                        break;
                    case 'study_year':
                        fields.push(<StudyYear name={inputName} personSelector={personSelector} def={inputDef}/>);
                        break;
                    default:
                        throw Error('Field no match');
                }
            }
        }
        return <FormSection name={'person_history'}>
            {fields}
        </FormSection>;
    }
}
