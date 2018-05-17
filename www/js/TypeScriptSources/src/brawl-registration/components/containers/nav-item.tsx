import * as React from 'react';
import Nav from '../helpers/tabs/nav';
import NameDisplay from '../displays/name-display';

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
