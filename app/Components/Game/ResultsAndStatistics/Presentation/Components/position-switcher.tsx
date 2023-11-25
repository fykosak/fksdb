import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { ACTION_SET_PARAMS } from '../../actions/presentation';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

export default function PositionSwitcher() {
    const categories = useSelector((state: Store) => state.data.categories);
    const category = useSelector((state: Store) => state.presentation.category);
    const cols = useSelector((state: Store) => state.presentation.cols);
    const delay = useSelector((state: Store) => state.presentation.delay);
    const position = useSelector((state: Store) => state.presentation.position);
    const rows = useSelector((state: Store) => state.presentation.rows);
    const teams = useSelector((state: Store) => state.data.teams);
    const dispatch = useDispatch();
    const getCategory = (): string => {
        const index = categories.indexOf(category);
        if (index === -1) {
            return categories[0];
        }
        if (index === categories.length) {
            return null;
        }
        return categories[index + 1];
    }

    const run = async (): Promise<void> | never => {
        let activeTeams;
        if (category) {
            activeTeams = teams.filter((team) => {
                return team.category === category;
            });
        } else {
            activeTeams = teams;
        }
        let newPosition = position + (cols * rows);

        let newCategory = category;
        if (newPosition >= activeTeams.length) {
            newCategory = getCategory();
            newPosition = 0;
        }
        await new Promise<void>((resolve) => {
            setTimeout(() => {
                dispatch({
                    data: {position: newPosition, category: newCategory},
                    type: ACTION_SET_PARAMS,
                });
                resolve();
            }, delay);
        });
        if (abortRun) {
            return;
        }
        await run();
    }
    let abortRun = false;
    useEffect(() => {
        run();
        return () => {
            abortRun = true;
        }
    });
    return null;
}
