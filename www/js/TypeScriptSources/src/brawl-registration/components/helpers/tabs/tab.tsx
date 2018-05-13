import * as React from 'react';

interface IProps {
    active: boolean;
    name: string;
    children?: any;
}

export default class Tab extends React.Component<IProps, {}> {

    public render() {
        const {active, name} = this.props;
        return <div className={'tab-pane fade show' + (active ? ' active' : '')}
                    id={name}
                    role="tabpanel"
                    aria-labelledby={name + '-tab'}>
            {...this.props.children}
        </div>;

    }
}
