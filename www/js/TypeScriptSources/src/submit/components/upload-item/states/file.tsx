import * as React from 'react';

interface IProps {
    name: string;
    href: string;
    submitId: number;
}

export default class File extends React.Component<IProps, {}> {

    public render() {
        return <div className="updatet-file">
            <span aria-hidden="true" className="pull-right" onClick={() => {
                console.log('delete');
            }}>&times;</span>

            <div className="text-center">
                <a href={this.props.href}>
                    <span className="display-1 w-100"><i className="fa fa-file-pdf-o"/></span>
                    <span className="d-block">{this.props.name}</span>
                </a>
            </div>

        </div>;
    }
}
