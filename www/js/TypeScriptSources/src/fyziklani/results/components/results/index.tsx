import * as React from 'react';
import ResultsShower from '../../../helpers/components/results-shower';
import AutoFilter from './filter/index';
import ResultsTable from './results-table';

interface IProps {
    mode: string;
}

export default class Results extends React.Component<IProps, {}> {

    public render() {
        const {mode} = this.props;
        return (
            <div>
                <ResultsShower className={(mode === 'presentation') ? 'inner-headline' : null}>
                    <ResultsTable/>
                </ResultsShower>
                {(mode === 'presentation') && (<AutoFilter/>)}
            </div>
        );
    }
}
