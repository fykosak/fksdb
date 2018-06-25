import * as React from 'react';
import { FormSection } from 'redux-form';
import FamilyName from './family-name';
import OtherName from './other-name';
import { ISectionProps } from '../interfaces';
import Email from './email';

export default class Person extends React.Component<ISectionProps, {}> {
    public render() {
        const fields = [];
        const {sectionDef, personSelector} = this.props;
        for (const inputName in sectionDef.fields) {
            if (sectionDef.fields.hasOwnProperty(inputName)) {
                const inputDef = sectionDef.fields[inputName];
                switch (inputName) {
                    case 'family_name':
                        fields.push(<FamilyName name={inputName} personSelector={personSelector} def={inputDef}/>);
                        break;
                    case 'other_name':
                        fields.push(<OtherName name={inputName} personSelector={personSelector} def={inputDef}/>);
                        break;
                    case 'email':
                        fields.push(<Email name={inputName} personSelector={personSelector} def={inputDef}/>);
                        break;
                    default:
                    // throw Error('Field no match');
                }
            }
        }
        return <FormSection name={'person'}>
            {fields}
        </FormSection>;
    }
}
