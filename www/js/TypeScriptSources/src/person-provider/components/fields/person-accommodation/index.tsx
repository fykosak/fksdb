import * as React from 'react';
import { FormSection } from 'redux-form';
import { ISectionProps } from '../interfaces';
import Accommodation from '../../../../brawl-registration/components/form/fields/accommodation';

export default class PersonAccommodation extends React.Component<ISectionProps, {}> {
    public render() {
        const fields = [];
        const {sectionDef, personSelector} = this.props;
        for (const inputName in sectionDef.fields) {
            if (sectionDef.fields.hasOwnProperty(inputName)) {
                const inputDef = sectionDef.fields[inputName];
                switch (inputName) {
                    case 'accommodation':
                        fields.push(<Accommodation type={personSelector.type} index={personSelector.index}/>);
                        // fields.push(<SchoolId name={inputName} personSelector={personSelector} def={inputDef}/>);
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
