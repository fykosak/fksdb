import * as React from 'react';
import { connect } from 'react-redux';
import { createFilters } from '../filter';
import FilterComponent from './Filter';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';
import { TranslatorContext } from '@translator/LangContext';

interface StateProps {
    categories: string[];
}

class FilterSelect extends React.Component<StateProps> {
    static contextType = TranslatorContext;
    public render() {
        const {categories} = this.props;
        const translator = this.context;
        return <>
            {createFilters(categories, translator).map((availableFilter, key) => {
                return <FilterComponent
                    key={key}
                    filter={availableFilter}
                />;
            })}
        </>;
    }
}

const mapStateToPros = (state: Store): StateProps => {
    return {
        categories: state.data.categories,
    };
};

export default connect(
    mapStateToPros,
    null,
)(FilterSelect);
