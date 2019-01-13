import * as React from 'react';

interface Props {
    icon: JSX.Element;
    children: any;
    className: string;
}

export default class Item extends React.Component<Props, {}> {

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
