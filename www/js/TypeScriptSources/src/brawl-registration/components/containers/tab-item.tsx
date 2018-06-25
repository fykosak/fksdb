import * as React from 'react';
import { connect } from 'react-redux';
import { FormSection } from 'redux-form';
import PersonProvider from '../../../person-provider/components/provider';
import NameDisplay from '../../../shared/components/displays/name';
import Tab from '../../../shared/components/tabs/tab';
import { IPersonSelector } from '../../middleware/price';

interface IProps {
    active: boolean;
    required?: boolean;
    personSelector: IPersonSelector;
}

class TabItem extends React.Component<IProps, {}> {
    public render() {
        const {active, personSelector} = this.props;
        return <FormSection key={personSelector.accessKey} name={personSelector.accessKey}>
            <Tab active={active} name={personSelector.accessKey}>
                <h2><NameDisplay personSelector={personSelector}/></h2>

                <PersonProvider personSelector={personSelector}/>
            </Tab>
        </FormSection>;
    }
}

const mapDispatchToProps = (): {} => {
    return {};
};

const mapStateToProps = (): {} => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(TabItem);
