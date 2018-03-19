'use strict';

$(document).ready(() => {

    const teamToString = (team) => {
        return '' + team.teamId + '-' + team.name + ' (' + team.category + ')';
    };

    const createPDFContent = (name, table) => {
        return {
            content: [
                {text: name, style: 'header'},
                {
                    table: {
                        headerRows: 0,
                        body: table,
                    },
                },
            ],
            styles: {
                fontSize: 20,
                header: {
                    fontSize: 40,
                    bold: true,
                },
            },
        };
    };

    const pdfAction = (pdf, action) => {
        switch (action) {
            default:
            case 'open':
                pdf.open();
                break;
            case'download':
                pdf.download();
                break;
            case'print':
                pdf.print();
                break;
        }
    };

    const createRoomPdf = (rooms, roomId, action) => {
        const definition = rooms.filter((room) => {
            return room.roomId === roomId;
        })[0];
        const pdf = pdfMake.createPdf(createPDFContent(definition.name, definition.design));
        pdfAction(pdf, action);
    };

    const createBuildingPdf = (building, action) => {
        const definition = buildings[building];
        const pdf = pdfMake.createPdf(createPDFContent(building, definition));
        pdfAction(pdf, action);
    };

    const renderUnRoutedTeams = (container, teams) => {
        if (teams.length) {
            const el = document.createElement('div');
            const h = document.createElement('h3');
            h.innerText = 'Neusadené týmy';
            el.appendChild(h);
            const p = document.createElement('span');
            p.innerText = unRoutedTeams.join(', ');
            el.appendChild(p);
            el.className = 'alert alert-danger';

            container.appendChild(el);
        }
    };

    const container = document.getElementById('routingDownload');
    if (!container) {
        return;
    }
    const teams = JSON.parse(container.getAttribute('data-teams'));
    const rooms = JSON.parse(container.getAttribute('data-rooms'));

    const unRoutedTeams = [];

    const buildings = {
        F: [],
        M: [],
        S: [],
    };

    rooms.forEach((room) => {
            const roomDefinition = [];
            for (let y = 0; y < room.y; y++) {
                roomDefinition[y] = [];
                for (let x = 0; x < room.x; x++) {
                    roomDefinition[y][x] = 'X';
                    teams.forEach((team) => {
                        if (team.roomId === room.roomId && team.x === x && team.y === y) {
                            roomDefinition[y][x] = teamToString(team);
                            const row = [team.teamId, team.name + ' (' + team.category + ')', room.name];
                            if (/^M/.test(room.name)) {
                                buildings.M.push(row);
                            }
                            if (/^D/.test(room.name)) {
                                buildings.S.push(row);
                            }
                            if (/^F/.test(room.name)) {
                                buildings.F.push(row);
                            }
                        }
                    });
                }
            }
            room.design = roomDefinition;
        },
    );

    teams.forEach((team) => {
        if (team.roomId === null || team.x === null || team.y === null) {
            unRoutedTeams.push(teamToString(team));
        }
    });

    renderUnRoutedTeams(container, unRoutedTeams);

    container.querySelectorAll('button').forEach((element) => {
        element.disabled = false;
        element.addEventListener('click', (event) => {
            switch (event.target.getAttribute('data-type')) {
                default:
                case 'room':
                    createRoomPdf(rooms, +event.target.getAttribute('data-roomId'), event.target.getAttribute('data-act'));
                    break;
                case'building':
                    createBuildingPdf(event.target.getAttribute('data-building'), event.target.getAttribute('data-act'));
            }

        });
    });
});
