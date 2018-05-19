import * as React from 'react';
import NameDisplay from '../displays/name';
import Nav from '../helpers/tabs/nav';

interface IProps {
    type: string;
    index: number;
    active: boolean;
}

export default class NavItem extends React.Component<IProps, {}> {
    public render() {
        const {index, type, active} = this.props;
        return <Nav active={active} name={(type + index)}>
            <NameDisplay type={type} index={index}/>
        </Nav>;
    }
}
