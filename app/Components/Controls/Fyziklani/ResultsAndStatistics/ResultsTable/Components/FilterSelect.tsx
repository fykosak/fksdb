import * as React from 'react';
import { connect } from 'react-redux';
import { FyziklaniStatisticsTableStore } from '../../ResultsTable/reducers';
import { createFilters } from '../filter';
import FilterComponent from './Filter';

interface StateProps {
    categories: string[];
}

class FilterSelect extends React.Component<StateProps> {

    public render() {
        const {categories} = this.props;
        return <>
            {createFilters(categories).map((availableFilter, key) => {
                return <FilterComponent
                    key={key}
                    filter={availableFilter}
                />;
            })}
        </>;
    }
}

const mapStateToPros = (state: FyziklaniStatisticsTableStore): StateProps => {
    return {
        categories: state.data.categories,
    };
};

export default connect(
    mapStateToPros,
    null,
)(FilterSelect);
