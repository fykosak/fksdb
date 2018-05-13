import * as React from 'react';

interface IProps {
    active: boolean;
    name: string;
    children?: any;
}

export default class Nav extends React.Component<IProps, {}> {
    public render() {
        const {active, name} = this.props;
        return <li className="nav-item">
            <a className={'nav-link' + (active ? ' active' : '')}
               id={'#' + name + '-tab'}
               data-toggle="tab"
               href={'#' + name} role="tab"
               aria-controls={name}
               aria-selected="true">
                {this.props.children}
            </a>
        </li>;
    }
}
