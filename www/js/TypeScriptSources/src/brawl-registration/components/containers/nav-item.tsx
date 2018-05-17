import * as React from 'react';
import NameDisplay from '../displays/name';
import Nav from '../helpers/tabs/nav';

interface IProps {
    type: string;
    index: number;
}

export default class NavItem extends React.Component<IProps, {}> {
    public render() {
        const {index, type} = this.props;
        return <Nav active={index === 0} name={('member' + index)}>
            <NameDisplay type={type} index={index}/>
        </Nav>;
    }
}
