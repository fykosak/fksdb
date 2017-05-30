export interface IFilter {
    room: string;
    category: string;
    name: string;
}

export const filters: Array<IFilter> = [
    {room: null, category: null, name: "ALL"},
    {room: null, category: 'A', name: "A"},
    {room: null, category: 'B', name: "B"},
    {room: null, category: 'C', name: "C"},
    {room: 'M1', category: null, name: "M1"},
    {room: 'M2', category: null, name: "M2"},
    {room: 'M3', category: null, name: "M3"},
    {room: 'M5', category: null, name: "M5"},
    {room: 'F1', category: null, name: "F1"},
    {room: 'F2', category: null, name: "F2"},
    {room: 'S3', category: null, name: "S3"},
    {room: 'S5', category: null, name: "S5"},
    {room: 'S9', category: null, name: "S9"},
];
