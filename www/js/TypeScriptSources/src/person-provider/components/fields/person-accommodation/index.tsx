import * as React from 'react';
import { FormSection } from 'redux-form';
import Accommodation from './accommodation';
import { ISectionProps } from '../interfaces';

export default class PersonAccommodation extends React.Component<ISectionProps, {}> {
    public render() {
        const fields = [];
        const {sectionDef, personSelector} = this.props;
        for (const inputName in sectionDef.fields) {
            if (sectionDef.fields.hasOwnProperty(inputName)) {
                const inputDef = sectionDef.fields[inputName];
                switch (inputName) {
                    case 'person_accommodation':
                        fields.push(<Accommodation personSelector={personSelector} inputDef={inputDef}/>);
                        break;
                    default:
                        throw Error('Field no match');
                }
            }
        }
        return <FormSection name={'person_history'}>
            <h3>{sectionDef.name}</h3>
            {fields}
        </FormSection>;
    }
}
