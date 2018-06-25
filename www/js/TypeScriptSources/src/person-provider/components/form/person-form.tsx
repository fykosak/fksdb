import * as React from 'react';

import { IPersonSelector } from '../../../brawl-registration/middleware/price';
import { ISectionDefinition } from '../fields/interfaces';
import Person from '../fields/person/';
import PersonInfo from '../fields/person-info';
import PersonHistory from '../fields/person-history';

interface IProps {
    personSelector: IPersonSelector;
    form: {
        [key: string]: ISectionDefinition;
    };
}

export default class PersonForm extends React.Component<IProps, {}> {

    public render() {
        const {form, personSelector} = this.props;
        const formComponent = [];
        for (const section in form) {
            if (form.hasOwnProperty(section)) {
                const sectionDef = form[section];
                switch (section) {
                    case 'person':
                        formComponent.push(<Person personSelector={personSelector} sectionDef={sectionDef}/>);
                        break;
                    case 'person_info':
                        formComponent.push(<PersonInfo personSelector={personSelector} sectionDef={sectionDef}/>);
                        break;
                    case 'person_history':
                        formComponent.push(<PersonHistory personSelector={personSelector} sectionDef={sectionDef}/>);
                        break;
                    default:
                        console.error('no match section');
                }
            }
        }
        return <div>
            {formComponent}
        </div>;
    }

}
