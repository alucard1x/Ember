/*
 * Google Charts, dressed for the Ember panel.
 *
 * The report pages draw onto a dark ground now, and Google Charts defaults to
 * a white canvas with black type — which rendered as bright slabs floating in
 * the panel. Each chart merges this object into its own options with
 * Object.assign, so per-chart settings (title, pieHole, is3D) still win.
 */
const EMBER_CHART = {
    backgroundColor: 'transparent',
    fontName: 'Montserrat',
    titleTextStyle: {
        color: '#f3efec',
        fontName: 'Montserrat',
        fontSize: 16,
        bold: false
    },
    legend: {
        textStyle: { color: '#b9aea8', fontName: 'Montserrat', fontSize: 12 }
    },
    chartArea: { width: '82%', height: '72%' },
    // Ember accent first, then the table-state colours, then quieter tints.
    colors: [
        '#b1462f', '#17594a', '#c9a227', '#8b807a', '#d8674c',
        '#6fbfa8', '#e2cd8a', '#5c8f80', '#a8102f', '#4a3b36'
    ],
    hAxis: {
        textStyle: { color: '#b9aea8', fontName: 'Montserrat' },
        titleTextStyle: { color: '#b9aea8', fontName: 'Montserrat' },
        gridlines: { color: 'rgba(255, 255, 255, 0.08)' },
        minorGridlines: { color: 'rgba(255, 255, 255, 0.04)' },
        baselineColor: 'rgba(255, 255, 255, 0.18)'
    },
    vAxis: {
        textStyle: { color: '#b9aea8', fontName: 'Montserrat' },
        titleTextStyle: { color: '#b9aea8', fontName: 'Montserrat' },
        gridlines: { color: 'rgba(255, 255, 255, 0.08)' },
        minorGridlines: { color: 'rgba(255, 255, 255, 0.04)' },
        baselineColor: 'rgba(255, 255, 255, 0.18)'
    },
    pieSliceTextStyle: { color: '#14100f', fontName: 'Montserrat' },
    pieSliceBorderColor: 'transparent'
};
