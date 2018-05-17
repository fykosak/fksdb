import * as React from 'react';
import NavItem from './nav-item';
import TabItem from './tab-item';

const persons = [
    {
        type: 'participant',
    },
    {
        type: 'participant',
    },
    {
        type: 'participant',
    },
    {
        type: 'participant',
    },
    {
        type: 'participant',
    },
    {
        type: 'teacher',
    },
];

export const getFieldName = (type: string, index: number): string => {
    return type + '[' + index + ']';
};

export default class PersonsContainer extends React.Component<{}, {}> {
    public render() {
        const body = persons.map((member, index) => {
            return <TabItem key={index} type={member.type} index={index}/>;
        });

        const tabs = persons.map((member, index) => {
            return <NavItem key={index} type={member.type} index={index}/>;
        });

        return <div>
            <ul className="nav nav-tabs" id="myTab" role="tablist">
                {tabs}
            </ul>
            <div className="tab-content" id="myTabContent">
                {body}
            </div>
        </div>;
    }
}
