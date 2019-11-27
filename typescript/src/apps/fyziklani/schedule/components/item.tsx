import * as React from 'react';

interface OwnProps {
    icon: JSX.Element;
    children: React.ReactNode;
    className: string;
}

export default class Item extends React.Component<OwnProps, {}> {

    public render() {
        const {icon} = this.props;

        return (
            <div className={'row ' + this.props.className}>
                <div className={'col-2 align-items-center d-flex text-center'}>
                    {icon}
                </div>
                <div className={'col-10'}>
                    {this.props.children}
                </div>
            </div>
        );
    }
}
