import * as React from 'react';
import { connect } from 'react-redux';
import { FormSection } from 'redux-form';
import PersonProvider from '../../../person-provider/components/provider';
import { getFieldName } from '../../middleware/person';
import { IPersonSelector } from '../../middleware/price';
import NameDisplay from '../displays/name';
import Tab from '../helpers/tabs/tab';

interface IProps {
    active: boolean;
    required?: boolean;
    personSelector: IPersonSelector;
}

class TabItem extends React.Component<IProps, {}> {
    public render() {
        const {active, personSelector, personSelector: {type, index}} = this.props;
        return <FormSection key={index} name={getFieldName(type, index)}>
            <Tab active={active} name={(type + index)}>
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
