import * as React from 'react';

interface IProps {
    name: string;
    href: string;
    submitId: number;
}

export default class UploadedFile extends React.Component<IProps, {}> {

    public render() {
        return <div className="updatet-file">
            <span aria-hidden="true" className="pull-right" onClick={() => {
                console.log('delete');
            }}>&times;</span>
            <a href={this.props.href}>
                <div className="text-center">
                    <span className="display-1 d-block"><i className="fa fa-file-pdf-o"/></span>
                    <span className="d-block">{this.props.name}</span>
                </div>
            </a>
        </div>;
    }
}
