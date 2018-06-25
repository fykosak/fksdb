import * as React from 'react';
import { FormSection } from 'redux-form';
import { ISectionProps } from '../interfaces';
import IdNumber from './id-number';

export default class PersonInfo extends React.Component<ISectionProps, {}> {
    public render() {
        const fields = [];
        const {sectionDef, personSelector} = this.props;
        for (const inputName in sectionDef.fields) {
            if (sectionDef.fields.hasOwnProperty(inputName)) {
                const inputDef = sectionDef.fields[inputName];
                switch (inputName) {
                    case 'id_number':
                        fields.push(<IdNumber name={inputName} personSelector={personSelector} def={inputDef}/>);
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
