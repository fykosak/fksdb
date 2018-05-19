import * as React from 'react';
import { connect } from 'react-redux';
import { IPersonDefinition } from '../../middleware/iterfaces';
import { IStore } from '../../reducers';
import NavItem from './nav-item';
import TabItem from './tab-item';

interface IState {
    personsDef?: IPersonDefinition[];
}

export const getFieldName = (type: string, index: number): string => {
    return type + '[' + index + ']';
};

class PersonsContainer extends React.Component<IState, {}> {
    public render() {
        const body = [];
        const tabs = [];
        if (!this.props.personsDef) {
            return null;
        }
        this.props.personsDef.forEach((member, index) => {
            const active = (index === 0);
            body.push(<TabItem key={index} active={active} type={member.type} index={index}/>);
            tabs.push(<NavItem key={index} active={active} type={member.type} index={index}/>);
        });

        return <div>
            <ul className="nav nav-tabs" id="form-tab" role="tablist">
                {tabs}
            </ul>
            <div className="tab-content" id="form-tab-content">
                {body}
            </div>
        </div>;
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore): IState => {
    return {
        personsDef: state.definitions.persons,
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(PersonsContainer);
