import { Chart} from 'https://cdn.jsdelivr.net/npm/chart.js/auto/+esm';
import { dayChart, weekChart, monthChart, yearChart } from './charts.js';
import moment from 'https://cdn.jsdelivr.net/npm/moment/+esm';

const data = await fetch('/controllers/api.php?action=stats').then(res => res.json());

const daysData = {};
const yearsData = {};

let selected = document.querySelector('.selected');

let index = 0;

for (const d of data) {
    // Split by day
    const week = moment(d.dateCommande).format('WW/YYYY');
    console.log(moment('2026-09-12').format('WW/YYYY'))
    const day = moment(d.dateCommande).isoWeekday();

    if (!daysData[week]) {
        daysData[week] = {
            1: { vente: 0, argent: 0 },
            2: { vente: 0, argent: 0 },
            3: { vente: 0, argent: 0 },
            4: { vente: 0, argent: 0 },
            5: { vente: 0, argent: 0 },
            6: { vente: 0, argent: 0 },
            7: { vente: 0, argent: 0 },
        };
    }

    daysData[week][day].vente += parseInt(d.quantite);
    daysData[week][day].argent = Math.round((daysData[week][day].argent + parseFloat(d.prixProduitHt) * parseInt(d.quantite)) * 100) / 100;

    // Split by year
    const year = moment(d.dateCommande).year();
    if (!yearsData[year]) {
        yearsData[year] = {
            vente: 0,
            argent: 0
        }
    }

    yearsData[year].vente += parseInt(d.quantite);
    yearsData[year].argent += parseFloat(d.prixProduitHt) * parseInt(d.quantite);
    yearsData[year].argent = Math.round(yearsData[year].argent * 100) / 100;
}

let [vente, argent] = [[], []];
let week = Object.keys(daysData).length - 1;
for (const d in Object.values(daysData)[week]) {
    vente.push(Object.values(daysData)[week][d].vente);
    argent.push(Object.values(daysData)[week][d].argent);
}

document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
let total = argent.reduce((a, b) => a + b, 0);
let formatted = Number.isInteger(total) ? total + '€' : total.toFixed(2) + '€';
document.getElementById('argents').innerHTML = formatted;

const canva = document.getElementById('stats');
let chart = new Chart(canva, dayChart(vente, argent));

document.getElementById('prev').disabled = Object.keys(daysData).length == 1 ? true : false;

function getWeekLabel(week) {
    return `Semaine du ${moment().isoWeek(week.split('/')[0]).startOf('isoWeek').format('DD/MM')} au ${moment().isoWeek(week.split('/')[0]).startOf('isoWeek').add(6, 'days').format('DD/MM')}`;
}

document.querySelector('article h3').innerHTML = getWeekLabel(Object.keys(daysData)[week]);

document.getElementById('prev').addEventListener('click', () => {
    index++;
    updateStats();

    if (index == 0) {
        document.getElementById('next').disabled = true;
        document.getElementById('prev').disabled = false;
    } else if (index == Object.keys(daysData).length - 1) {
        document.getElementById('next').disabled = false;
        document.getElementById('prev').disabled = true;
    }
})

document.getElementById('next').addEventListener('click', () => {
    index--;
    updateStats();

    if (index == 0) {
        document.getElementById('next').disabled = true;
        document.getElementById('prev').disabled = false;
    } else if (index == Object.keys(daysData).length - 1) {
        document.getElementById('next').disabled = false;
        document.getElementById('prev').disabled = true;
    }
})

function updateStats() {
    chart.destroy();
    [vente, argent] = [[], []];

    switch (selected.innerHTML) {
        case 'Journalier':
            week = Object.keys(daysData).length - 1 - index;
            for (const d in Object.values(daysData)[week]) {
                vente.push(Object.values(daysData)[week][d].vente);
                argent.push(Object.values(daysData)[week][d].argent);
            }

            document.querySelector('article h3').innerHTML = getWeekLabel(Object.keys(daysData)[week]);

            chart = new Chart(canva, dayChart(vente, argent));
            break;
    }

    document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
    total = argent.reduce((a, b) => a + b, 0);
    formatted = Number.isInteger(total) ? total + '€' : total.toFixed(2) + '€';
    document.getElementById('argents').innerHTML = formatted;
}

document.querySelectorAll('button:not(#prev, #next)').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelector('.selected:not(#prev, #next)').classList.remove('selected');
        btn.classList.add('selected');

        selected = document.querySelector('.selected');

        chart.destroy();
        [vente, argent] = [[], []];

        index = 0;

        switch (selected.innerHTML) {
            case 'Journalier':
                week = Object.keys(daysData).length - 1;
                for (const d in Object.values(daysData)[week]) {
                    vente.push(Object.values(daysData)[week][d].vente);
                    argent.push(Object.values(daysData)[week][d].argent);
                }

                chart = new Chart(canva, dayChart(vente, argent));
                document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
                document.getElementById('argents').innerHTML = argent.reduce((a, b) => a + b, 0) + '€';

                document.getElementById('prev').disabled = false;
                document.getElementById('next').disabled = true;
                break;

            case 'Hebdomadaire':
                [vente, argent] = [[8, 5, 2, 3, 2], [21, 45, 13, 8, 13]];
                chart = new Chart(canva, weekChart(vente, argent));
                document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
                document.getElementById('argents').innerHTML = argent.reduce((a, b) => a + b, 0) + '€';

                document.getElementById('prev').disabled = false;
                document.getElementById('next').disabled = true;
                break;
            
            case 'Mensuel':
                [vente, argent] = [[12, 19, 3, 5, 2, 3, 2, 8, 4, 1, 2, 0], [45, 41, 21, 45, 13, 5, 13, 20, 14, 13, 10, 0]];
                chart = new Chart(canva, monthChart(vente, argent));
                document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
                document.getElementById('argents').innerHTML = argent.reduce((a, b) => a + b, 0) + '€';

                document.getElementById('prev').disabled = false;
                document.getElementById('next').disabled = true;
                break;
            
            case 'Annuel':
                for (const y in yearsData) {
                    vente.push(yearsData[y].vente);
                    argent.push(yearsData[y].argent);
                }

                chart = new Chart(canva, yearChart(vente, argent, Object.keys(yearsData)));

                document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
                total = argent.reduce((a, b) => a + b, 0);
                formatted = Number.isInteger(total) ? total + '€' : total.toFixed(2) + '€';
                document.getElementById('argents').innerHTML = formatted;

                document.getElementById('prev').disabled = true;
                document.getElementById('next').disabled = true;
                break;
        }
    })
})