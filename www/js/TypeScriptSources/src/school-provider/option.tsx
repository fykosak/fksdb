import * as React from 'react';

interface IProps {
    label: string;
    region: string;
}

export default class Option extends React.Component<IProps, {}> {

    public render() {
        const {label, region} = this.props;

        return <span><img style={{height: '1em'}}
                          className="mr-2"
                          alt=""
                          src={'/images/flags/4x3/' + region.toLowerCase() + '.svg'}
        />{label}</span>;
    }
}
